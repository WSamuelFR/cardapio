<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MenuController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->with(['products' => function ($query) {
                $query->where(function ($q) {
                    $q->where('is_combo', false)
                      ->orWhereHas('category', function ($catQ) {
                          $catQ->where('slug', 'combos-especiais');
                      });
                })->orderBy('name', 'asc');
            }])
            ->get();

        // 1. Promoções Imperdíveis (itens individuais em oferta)
        $promotions = Product::where('is_available', true)
            ->where('is_promotion', true)
            ->where('is_combo', false)
            ->with('category')
            ->get();

        // 2. Combos do Dia (combos cadastrados e disponíveis)
        $combos = Product::where('is_available', true)
            ->where('is_combo', true)
            ->get();

        // 3. Mais Pedidos (Globalmente ordenados pelas vendas reais no site)
        $popularProductIds = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->whereNotNull('product_id')
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->pluck('product_id');

        if ($popularProductIds->isNotEmpty()) {
            $idsOrdered = $popularProductIds->implode(',');
            $popular = Product::whereIn('id', $popularProductIds)
                ->where('is_available', true)
                ->orderByRaw("FIELD(id, {$idsOrdered})")
                ->get();
        } else {
            $popular = collect();
        }

        // Caso o site ainda tenha poucos pedidos reais, complementa com os produtos disponíveis
        if ($popular->count() < 6) {
            $needed = 6 - $popular->count();
            $existingIds = $popular->pluck('id');
            $fallback = Product::where('is_available', true)
                ->where('is_combo', false)
                ->whereNotIn('id', $existingIds)
                ->limit($needed)
                ->get();
            $popular = $popular->merge($fallback);
        }

        return response()->json([
            'categories' => $categories,
            'promotions' => $promotions,
            'combos' => $combos,
            'popular' => $popular,
            'menu_settings' => [
                'pizza_sizes' => \App\Models\SystemSetting::getSetting('pizza_sizes', []),
                'pizza_borders' => \App\Models\SystemSetting::getSetting('pizza_borders', []),
                'monte_pastel' => \App\Models\SystemSetting::getSetting('monte_pastel', []),
                'store_whatsapp' => \App\Models\SystemSetting::getSetting('store_whatsapp', '(83) 99999-9999'),
                'store_info' => \App\Models\SystemSetting::getSetting('store_info', [
                    'name' => 'AKY LANCHES',
                    'slogan' => 'Cardápio Digital & Pedidos',
                    'address' => 'Rua Principal, Centro - Teixeira - PB',
                    'hours' => 'Todos os dias, das 18h00 às 23h30',
                    'document' => '',
                ]),
            ],
        ]);
    }

    public function createOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:150',
            'customer_phone' => 'required|string|max:30',
            'delivery_cep' => 'required|string|max:10',
            'delivery_address' => 'required|string|max:255',
            'delivery_complement' => 'nullable|string|max:100',
            'delivery_reference' => 'nullable|string|max:150',
            'notes' => 'nullable|string|max:500',
            'payment_method' => 'nullable|string|in:dinheiro,pix,cartao_credito,cartao_debito',
            'needs_change' => 'nullable|boolean',
            'change_for' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:50',
            'items.*.custom_name' => 'nullable|string|max:255',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:500',
        ]);

        $cleanCustomerName = trim(mb_strtoupper(preg_replace('/[^\p{L}\s]/u', '', $validated['customer_name'])));
        if (empty($cleanCustomerName)) {
            return response()->json([
                'message' => 'Nome completo inválido. Digite apenas letras.'
            ], 422);
        }
        $validated['customer_name'] = $cleanCustomerName;

        // Validação de Regra de Negócio: Entrega apenas em Teixeira - PB (CEP base 58735-000)
        $cepDigits = preg_replace('/\D/', '', $validated['delivery_cep']);
        $isTeixeira = false;

        if (str_starts_with($cepDigits, '58735')) {
            $isTeixeira = true;
        } else {
            try {
                $response = Http::timeout(3)->get("https://viacep.com.br/ws/{$cepDigits}/json/");
                if ($response->successful()) {
                    $data = $response->json();
                    if (!empty($data['localidade']) && strcasecmp($data['localidade'], 'Teixeira') === 0 && (!empty($data['uf']) && strcasecmp($data['uf'], 'PB') === 0)) {
                        $isTeixeira = true;
                    }
                }
            } catch (\Throwable $e) {
                // Em caso de falha de conexão com a API externa, apenas o CEP 58735 é aceito
            }
        }

        if (!$isTeixeira) {
            return response()->json([
                'message' => 'desculpe, não entregamos na sua região!'
            ], 422);
        }

        return DB::transaction(function () use ($validated) {
            $total = 0.00;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                if (!$product->is_available) {
                    abort(422, "O produto '{$product->name}' não está disponível no momento.");
                }

                $unitPrice = (float) $product->effective_price;
                if (!empty($item['unit_price']) && is_numeric($item['unit_price']) && (float) $item['unit_price'] >= 5.0) {
                    $unitPrice = (float) $item['unit_price'];
                }

                $quantity = (int) $item['quantity'];
                $subtotal = $unitPrice * $quantity;
                $total += $subtotal;

                $nameSnapshot = !empty($item['custom_name']) ? trim($item['custom_name']) : $product->name;
                if (!empty($item['notes'])) {
                    $nameSnapshot .= ' [' . trim($item['notes']) . ']';
                }

                $itemsData[] = [
                    'product_id' => $product->id,
                    'product_name_snapshot' => $nameSnapshot,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ];
            }

            $orderNumber = 'AKY-' . date('Ymd') . '-' . strtoupper(Str::random(4));

            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'delivery_cep' => $validated['delivery_cep'],
                'delivery_address' => $validated['delivery_address'],
                'delivery_complement' => $validated['delivery_complement'] ?? null,
                'delivery_reference' => $validated['delivery_reference'] ?? null,
                'status' => 'recebido',
                'total' => $total,
                'notes' => $validated['notes'] ?? null,
                'payment_method' => $validated['payment_method'] ?? 'dinheiro',
                'needs_change' => $validated['needs_change'] ?? false,
                'change_for' => !empty($validated['needs_change']) ? ($validated['change_for'] ?? null) : null,
            ]);

            foreach ($itemsData as $item) {
                $item['order_id'] = $order->id;
                OrderItem::create($item);
            }

            return response()->json([
                'message' => 'Pedido realizado com sucesso!',
                'order' => $order->load('items'),
            ], 201);
        });
    }

    public function trackOrder(string $orderNumber): JsonResponse
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['items', 'motoboy:id,name'])
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Pedido não encontrado.'], 404);
        }

        return response()->json(['order' => $order]);
    }
}

