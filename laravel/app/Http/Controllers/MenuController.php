<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
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
            if (DB::connection()->getDriverName() === 'mysql') {
                $idsOrdered = $popularProductIds->implode(',');
                $popular = Product::whereIn('id', $popularProductIds)
                    ->where('is_available', true)
                    ->orderByRaw("FIELD(id, {$idsOrdered})")
                    ->get();
            } else {
                // SQLite compatível
                $popular = Product::whereIn('id', $popularProductIds)
                    ->where('is_available', true)
                    ->get()
                    ->sortBy(function ($model) use ($popularProductIds) {
                        return array_search($model->id, $popularProductIds->toArray());
                    })->values();
            }
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

        // Buscar avaliações de clientes de 4 estrelas ou mais vinculadas aos produtos e com permissão pública
        $positiveReviews = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->whereNotNull('orders.food_rating')
            ->where('orders.food_rating', '>=', 4)
            ->where('orders.allow_public_review', true)
            ->whereNotNull('order_items.product_id')
            ->select(
                'order_items.product_id',
                'orders.food_rating',
                'orders.review_comment',
                'orders.customer_name',
                'orders.review_show_name',
                'orders.reviewed_at',
                'orders.created_at'
            )
            ->orderBy('orders.reviewed_at', 'desc')
            ->get()
            ->groupBy('product_id');

        $reviewsByProduct = [];
        foreach ($positiveReviews as $productId => $reviews) {
            $formattedReviews = [];
            foreach ($reviews->take(10) as $rev) {
                if (!empty($rev->review_show_name)) {
                    $nameParts = explode(' ', trim($rev->customer_name));
                    $displayName = ucfirst(mb_strtolower($nameParts[0] ?? 'Cliente'));
                } else {
                    $displayName = 'Cliente Anônimo';
                }

                $formattedReviews[] = [
                    'customer_name' => $displayName,
                    'is_anonymous' => empty($rev->review_show_name),
                    'food_rating' => (int) $rev->food_rating,
                    'review_comment' => $rev->review_comment,
                    'date' => date('d/m/Y', strtotime($rev->reviewed_at ?: $rev->created_at)),
                ];
            }

            $avgRating = round($reviews->avg('food_rating'), 1);
            $reviewsByProduct[$productId] = [
                'average' => $avgRating,
                'count' => $reviews->count(),
                'reviews' => $formattedReviews,
            ];
        }

        return response()->json([
            'categories' => $categories,
            'promotions' => $promotions,
            'combos' => $combos,
            'popular' => $popular,
            'product_reviews' => $reviewsByProduct,
            'menu_settings' => [
                'pizza_sizes' => \App\Models\SystemSetting::getSetting('pizza_sizes', []),
                'pizza_borders' => \App\Models\SystemSetting::getSetting('pizza_borders', []),
                'monte_pastel' => \App\Models\SystemSetting::getSetting('monte_pastel', []),
                'monte_hamburguer' => \App\Models\SystemSetting::getSetting('monte_hamburguer', []),
                'combo_do_seu_jeito' => \App\Models\SystemSetting::getSetting('combo_do_seu_jeito', []),
                'store_whatsapp' => \App\Models\SystemSetting::getSetting('store_whatsapp', '(83) 99631-7878'),
                'store_info' => \App\Models\SystemSetting::getSetting('store_info', [
                    'name' => 'AKY LANCHES',
                    'slogan' => 'Cardápio Digital & Pedidos',
                    'address' => 'Rua Principal, Centro - Teixeira - PB',
                    'hours' => 'Todos os dias, das 18h00 às 23h30',
                    'document' => '',
                ]),
                'store_hours' => \App\Models\SystemSetting::getSetting('store_hours', (new AdminController)->defaultStoreHours()),
                'store_status' => AdminController::calculateStoreStatus(\App\Models\SystemSetting::getSetting('store_hours', (new AdminController)->defaultStoreHours())),
                'whatsapp_messages' => \App\Models\SystemSetting::getSetting('whatsapp_messages', []),
                'print_settings' => \App\Models\SystemSetting::getSetting('print_settings', (new AdminController)->defaultPrintSettings()),
            ],
        ]);
    }

    public function createOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_type' => 'nullable|string|in:entrega,retirada',
            'customer_name' => 'required|string|max:150',
            'customer_phone' => 'required|string|max:30',
            'customer_cpf' => 'required|string|max:20',
            'delivery_cep' => 'nullable|string|max:10',
            'delivery_address' => 'nullable|string|max:255',
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

        if (!$this->validateCpf($validated['customer_cpf'] ?? '')) {
            return response()->json([
                'message' => 'CPF inválido. Por favor, digite um CPF válido para confirmar o pedido.'
            ], 422);
        }

        $encryptedDocument = Crypt::encryptString(preg_replace('/\D/', '', $validated['customer_cpf']));

        $cleanCustomerName = trim(mb_strtoupper(preg_replace('/[^\p{L}\s]/u', '', $validated['customer_name'])));
        if (empty($cleanCustomerName)) {
            return response()->json([
                'message' => 'Nome completo inválido. Digite apenas letras.'
            ], 422);
        }
        $validated['customer_name'] = $cleanCustomerName;

        // Validação de horário de funcionamento, entrega e retirada
        $adminCtrl = new AdminController();
        $storeHours = \App\Models\SystemSetting::getSetting('store_hours', $adminCtrl->defaultStoreHours());
        $storeStatus = AdminController::calculateStoreStatus($storeHours);

        if (!$storeStatus['is_open']) {
            $msg = $storeStatus['notice_message'] ?? 'Estamos fechados no momento para novos pedidos.';
            return response()->json(['message' => $msg], 422);
        }

        $isPickup = ($validated['order_type'] ?? 'entrega') === 'retirada';

        if (!$isPickup && !$storeStatus['can_delivery']) {
            $msg = $storeStatus['notice_message'] ?? 'O serviço de entrega está pausado no momento. Por favor, escolha a opção de Retirada no Balcão!';
            return response()->json(['message' => $msg], 422);
        }

        if ($isPickup && !$storeStatus['can_pickup']) {
            $msg = $storeStatus['notice_message'] ?? 'A opção de retirada no balcão não está disponível no momento.';
            return response()->json(['message' => $msg], 422);
        }

        $storeInfo = \App\Models\SystemSetting::getSetting('store_info', []);
        $storeAddress = !empty($storeInfo['address']) ? trim($storeInfo['address']) : 'Rua Principal, Centro - Teixeira - PB';

        if (!$isPickup) {
            $validated['delivery_cep'] = '58735-000';
            if (empty($validated['delivery_address'])) {
                return response()->json([
                    'message' => 'Por favor, informe a rua, número e bairro para a entrega em Teixeira - PB.'
                ], 422);
            }
        } else {
            $validated['delivery_cep'] = '58735-000';
            $validated['delivery_address'] = 'RETIRADA NO ESTABELECIMENTO (' . $storeAddress . ')';
            $validated['delivery_complement'] = null;
            $validated['delivery_reference'] = 'Balcão da Loja';
        }

        return DB::transaction(function () use ($validated, $encryptedDocument) {
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
                'customer_document_encrypted' => $encryptedDocument,
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
                'store_whatsapp' => \App\Models\SystemSetting::getSetting('store_whatsapp', '(83) 99999-9999'),
            ], 201);
        });
    }

    public function trackOrder(string $orderNumber): JsonResponse
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['items', 'motoboy:id,name,phone'])
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Pedido não encontrado.'], 404);
        }

        $whatsappMessages = \App\Models\SystemSetting::getSetting('whatsapp_messages', (new AdminController)->defaultWhatsAppMessages());
        $doubtTemplate = null;
        if (is_array($whatsappMessages)) {
            foreach ($whatsappMessages as $msg) {
                if (isset($msg['trigger']) && $msg['trigger'] === 'duvida_pedido' && !empty($msg['is_active'])) {
                    $doubtTemplate = $msg['text'];
                    break;
                }
            }
        }

        return response()->json([
            'order' => $order,
            'store_whatsapp' => \App\Models\SystemSetting::getSetting('store_whatsapp', '(83) 99631-7878'),
            'doubt_whatsapp_template' => $doubtTemplate,
        ]);
    }

    /**
     * Confirmação de recebimento e avaliação do pedido pelo cliente no rastreio
     */
    public function confirmAndRateOrder(Request $request, string $orderNumber): JsonResponse
    {
        $order = Order::where('order_number', $orderNumber)->first();

        if (!$order) {
            return response()->json(['message' => 'Pedido não encontrado.'], 404);
        }

        if ($order->status === 'cancelado') {
            return response()->json(['message' => 'Não é possível avaliar um pedido cancelado.'], 422);
        }

        $validated = $request->validate([
            'confirm_delivery' => 'nullable|boolean',
            'food_rating' => 'required|integer|min:1|max:5',
            'delivery_rating' => 'required|integer|min:1|max:5',
            'review_comment' => 'nullable|string|max:1000',
            'allow_public_review' => 'nullable|boolean',
            'review_show_name' => 'nullable|boolean',
        ]);

        if (empty($order->customer_confirmed_at)) {
            $order->customer_confirmed_at = now();
        }

        $order->food_rating = $validated['food_rating'];
        $order->delivery_rating = $validated['delivery_rating'];
        $order->review_comment = !empty($validated['review_comment']) ? trim($validated['review_comment']) : null;
        $order->allow_public_review = array_key_exists('allow_public_review', $validated) ? (bool)$validated['allow_public_review'] : true;
        $order->review_show_name = array_key_exists('review_show_name', $validated) ? (bool)$validated['review_show_name'] : true;
        $order->reviewed_at = now();

        $isPickup = $order->delivery_address && str_contains($order->delivery_address, 'RETIRADA');
        $requireConfirmation = (bool) \App\Models\SystemSetting::getSetting('require_motoboy_confirmation', true);

        // Se for retirada OU se o motoboy já confirmou OU se a regra de motoboy estiver desligada:
        if ($isPickup || !empty($order->motoboy_confirmed_at) || !$requireConfirmation) {
            $order->status = 'concluido';
        }

        $order->save();

        return response()->json([
            'message' => 'Obrigado por confirmar o recebimento e avaliar o seu pedido!',
            'order' => $order->load(['items', 'motoboy:id,name,phone']),
        ]);
    }

    /**
     * Validação Matemática Oficial do CPF (Módulo 11)
     */
    private function validateCpf(string $cpf): bool
    {
        $cpf = preg_replace('/\D/', '', $cpf);
        if (strlen($cpf) !== 11) {
            return false;
        }
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }
        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) {
                $d += (int) $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ((int) $cpf[$t] !== $d) {
                return false;
            }
        }
        return true;
    }
}

