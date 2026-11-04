<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncController extends Controller
{
    /**
     * Espelhamento completo do Catálogo da Nuvem para o SQLite Local
     * Retorna produtos, categorias, montadores e configurações com hash MD5
     */
    public function getCatalog(): JsonResponse
    {
        $categories = Category::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->with(['products' => function ($q) {
                $q->where('is_available', true)->orderBy('name', 'asc');
            }])
            ->get();

        $products = Product::where('is_available', true)
            ->with('category')
            ->orderBy('name', 'asc')
            ->get();

        $settings = [
            'pizza_sizes' => SystemSetting::getSetting('pizza_sizes', []),
            'pizza_borders' => SystemSetting::getSetting('pizza_borders', []),
            'monte_pastel' => SystemSetting::getSetting('monte_pastel', []),
            'store_info' => SystemSetting::getSetting('store_info', [
                'name' => 'AKY LANCHES',
                'slogan' => 'Cardápio Digital & Pedidos',
                'address' => 'Rua Principal, Centro - Teixeira - PB',
                'hours' => 'Todos os dias, das 18h00 às 23h30',
                'document' => '',
            ]),
            'store_whatsapp' => SystemSetting::getSetting('store_whatsapp', '(83) 99631-7878'),
            'require_motoboy_confirmation' => (bool) SystemSetting::getSetting('require_motoboy_confirmation', true),
            'print_settings' => SystemSetting::getSetting('print_settings', (new AdminController)->defaultPrintSettings()),
        ];

        $payload = [
            'categories' => $categories,
            'products' => $products,
            'settings' => $settings,
        ];

        $catalogHash = md5(json_encode($payload));

        return response()->json([
            'catalog_hash' => $catalogHash,
            'timestamp' => now()->toIso8601String(),
            'data' => $payload,
        ]);
    }

    /**
     * Sincronização Idempotente da Fila Local para a Nuvem (Push)
     * Processa em lote ou pedido individual baseado no UUID Universal
     */
    public function syncOrders(Request $request): JsonResponse
    {
        $rawOrders = $request->input('orders');
        if (!is_array($rawOrders)) {
            $rawOrders = [$request->all()];
        }

        $results = [];

        foreach ($rawOrders as $orderData) {
            if (empty($orderData) || !is_array($orderData)) {
                continue;
            }

            $uuid = $orderData['uuid'] ?? null;
            if (empty($uuid)) {
                $results[] = [
                    'status' => 'error',
                    'message' => 'UUID universal é obrigatório para sincronização.',
                ];
                continue;
            }

            $clientUpdatedAt = !empty($orderData['client_updated_at'])
                ? Carbon::parse($orderData['client_updated_at'])
                : now();

            try {
                $syncedOrder = DB::transaction(function () use ($orderData, $uuid, $clientUpdatedAt) {
                    $existing = Order::where('uuid', $uuid)->first();

                    if ($existing) {
                        // Idempotência: só altera dados se a requisição do cliente for mais recente
                        if ($clientUpdatedAt->greaterThanOrEqualTo($existing->updated_at)) {
                            $updateData = [];

                            if (!empty($orderData['status']) && in_array($orderData['status'], ['recebido', 'em_preparacao', 'saiu_para_entrega', 'concluido', 'cancelado'])) {
                                $updateData['status'] = $orderData['status'];
                            }
                            if (isset($orderData['notes'])) {
                                $updateData['notes'] = $orderData['notes'];
                            }
                            if (isset($orderData['motoboy_id'])) {
                                $updateData['motoboy_id'] = $orderData['motoboy_id'];
                            }

                            $updateData['client_updated_at'] = $clientUpdatedAt;
                            $updateData['sync_status'] = 'synced';

                            $existing->update($updateData);
                        }

                        return [
                            'uuid' => $uuid,
                            'order_number' => $existing->order_number,
                            'action' => 'idempotent_ok',
                            'server_status' => $existing->status,
                            'updated_at' => $existing->updated_at->toIso8601String(),
                        ];
                    }

                    // Se não existe, cria o pedido com o UUID especificado
                    $orderNumber = $orderData['order_number'] ?? ('AKY-' . date('Ymd') . '-' . strtoupper(Str::random(4)));
                    if (Order::where('order_number', $orderNumber)->exists()) {
                        $orderNumber = 'AKY-' . date('Ymd') . '-' . strtoupper(Str::random(4));
                    }

                    $encryptedDocument = null;
                    if (!empty($orderData['customer_cpf'])) {
                        $encryptedDocument = Crypt::encryptString(preg_replace('/\D/', '', $orderData['customer_cpf']));
                    }

                    $newOrder = Order::create([
                        'uuid' => $uuid,
                        'order_number' => $orderNumber,
                        'origin' => $orderData['origin'] ?? 'desktop_pos',
                        'sync_status' => 'synced',
                        'customer_name' => $orderData['customer_name'] ?? 'Balcão / Terminal',
                        'customer_phone' => $orderData['customer_phone'] ?? '(83) 99631-7878',
                        'customer_document_encrypted' => $encryptedDocument,
                        'delivery_cep' => $orderData['delivery_cep'] ?? null,
                        'delivery_address' => $orderData['delivery_address'] ?? 'RETIRADA NO BALCÃO',
                        'delivery_complement' => $orderData['delivery_complement'] ?? null,
                        'delivery_reference' => $orderData['delivery_reference'] ?? null,
                        'status' => $orderData['status'] ?? 'recebido',
                        'total' => $orderData['total'] ?? 0.00,
                        'notes' => $orderData['notes'] ?? null,
                        'payment_method' => $orderData['payment_method'] ?? 'dinheiro',
                        'needs_change' => !empty($orderData['needs_change']),
                        'change_for' => !empty($orderData['change_for']) ? $orderData['change_for'] : null,
                        'client_updated_at' => $clientUpdatedAt,
                    ]);

                    // Insere itens
                    $items = $orderData['items'] ?? [];
                    foreach ($items as $item) {
                        OrderItem::create([
                            'uuid' => $item['uuid'] ?? (string) Str::uuid(),
                            'order_id' => $newOrder->id,
                            'product_id' => $item['product_id'] ?? null,
                            'product_name_snapshot' => $item['product_name_snapshot'] ?? ($item['custom_name'] ?? 'Item'),
                            'quantity' => $item['quantity'] ?? 1,
                            'unit_price' => $item['unit_price'] ?? 0.00,
                            'subtotal' => $item['subtotal'] ?? (($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0.00)),
                        ]);
                    }

                    return [
                        'uuid' => $uuid,
                        'order_number' => $newOrder->order_number,
                        'action' => 'created',
                        'server_status' => $newOrder->status,
                        'created_at' => $newOrder->created_at->toIso8601String(),
                    ];
                });

                $results[] = $syncedOrder;
            } catch (\Throwable $e) {
                $results[] = [
                    'uuid' => $uuid,
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'synced' => true,
            'timestamp' => now()->toIso8601String(),
            'results' => $results,
        ]);
    }

    /**
     * Obtenção de Pedidos Novos / Atualizados na Nuvem (Pull Delta)
     * Permite que o Terminal Desktop receba pedidos feitos pelos clientes na Web
     */
    public function getOrdersDelta(Request $request): JsonResponse
    {
        $since = $request->query('since');
        $query = Order::with(['items', 'motoboy:id,name,phone'])
            ->orderBy('updated_at', 'desc');

        if (!empty($since)) {
            try {
                $sinceDate = Carbon::parse($since);
                $query->where('updated_at', '>=', $sinceDate);
            } catch (\Exception $e) {
                // Se timestamp for inválido, traz os mais recentes
                $query->limit(50);
            }
        } else {
            // Se não especificou 'since', retorna últimas 24 horas ou últimos 60 pedidos
            $query->where('created_at', '>=', now()->subHours(24))->limit(100);
        }

        $orders = $query->get();

        return response()->json([
            'timestamp' => now()->toIso8601String(),
            'count' => $orders->count(),
            'orders' => $orders,
        ]);
    }
}

