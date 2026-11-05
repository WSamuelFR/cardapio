<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    private function checkAdmin(): void
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso não autorizado para esta operação.');
        }
    }

    private function checkStaff(): void
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['admin', 'attendant'])) {
            abort(401, 'Faça login para acessar o painel.');
        }
    }

    private function checkKitchenOrStaff(): void
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['admin', 'attendant', 'kitchen'])) {
            abort(401, 'Acesso restrito para a equipe da cozinha e administração.');
        }
    }

    // --- PEDIDOS (Admin e Atendente) ---

    public function getOrders(Request $request): JsonResponse
    {
        $this->checkStaff();

        $query = Order::with(['items.product', 'motoboy:id,name', 'kitchenUser:id,name'])->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(30);

        return response()->json($orders);
    }

    public function updateOrderStatus(Request $request, int $id): JsonResponse
    {
        $this->checkStaff();

        $validated = $request->validate([
            'status' => 'required|in:recebido,em_preparacao,saiu_para_entrega,concluido,cancelado',
        ]);

        $order = Order::findOrFail($id);

        if ($order->status === 'concluido') {
            return response()->json([
                'message' => 'Este pedido já foi concluído e não pode sofrer alterações de status.'
            ], 422);
        }

        $isPickup = $order->delivery_address && str_contains($order->delivery_address, 'RETIRADA');

        // Validação 1: Pedido de Entrega saindo para entrega precisa de motoboy
        if ($validated['status'] === 'saiu_para_entrega' && !$isPickup && empty($order->motoboy_id)) {
            return response()->json([
                'message' => 'Para alterar o status para "Saiu para Entrega", é obrigatório atribuir um entregador (motoboy) ao pedido de entrega.'
            ], 422);
        }

        // Validação 2: Se a regra estiver ativa, pedidos de entrega requerem confirmação do motoboy ou desempate pelo atendente/admin
        $requireConfirmation = (bool) SystemSetting::getSetting('require_motoboy_confirmation', true);
        if ($validated['status'] === 'concluido' && !$isPickup && $requireConfirmation) {
            $user = Auth::user();
            // Se for motoboy, ele marca a confirmação dele
            if ($user && $user->role === 'motoboy') {
                $order->motoboy_confirmed_at = now();
            } else {
                // Atendente ou Admin atuando como desempate/validador
                // Se nenhum dos dois confirmou ainda e a confirmação é estrita, podemos registrar que o staff validou
                // O atendente tem permissão para desempatar e concluir!
            }
        }

        $order->status = $validated['status'];
        if ($validated['status'] === 'concluido') {
            if (!$order->customer_confirmed_at && $isPickup) {
                $order->customer_confirmed_at = now();
            }
        }
        $order->save();

        return response()->json([
            'message' => 'Status do pedido atualizado com sucesso.',
            'order' => $order->load('items'),
        ]);
    }

    public function deleteOrder(int $id): JsonResponse
    {
        $this->checkStaff();

        $order = Order::findOrFail($id);

        if ($order->status !== 'cancelado') {
            return response()->json([
                'message' => 'Apenas pedidos cancelados podem ser excluídos do sistema.'
            ], 422);
        }

        $order->delete();

        return response()->json([
            'message' => 'Pedido cancelado excluído com sucesso.'
        ]);
    }

    // --- CATEGORIAS (Exclusivo Admin) ---

    public function getCategories(): JsonResponse
    {
        $this->checkAdmin();
        $categories = Category::withCount('products')->orderBy('sort_order', 'asc')->get();
        return response()->json($categories);
    }

    public function saveCategory(Request $request, ?int $id = null): JsonResponse
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $slug = Str::slug($validated['name']);

        if ($id) {
            $category = Category::findOrFail($id);
            $category->update([
                'name' => $validated['name'],
                'slug' => $slug,
                'sort_order' => $validated['sort_order'] ?? $category->sort_order,
                'is_active' => $validated['is_active'] ?? $category->is_active,
            ]);
        } else {
            $category = Category::create([
                'name' => $validated['name'],
                'slug' => $slug,
                'sort_order' => $validated['sort_order'] ?? 0,
                'is_active' => $validated['is_active'] ?? true,
            ]);
        }

        return response()->json([
            'message' => 'Categoria salva com sucesso.',
            'category' => $category,
        ]);
    }

    public function toggleCategoryActive(int $id): JsonResponse
    {
        $this->checkAdmin();
        $category = Category::findOrFail($id);
        $category->is_active = !$category->is_active;
        $category->save();

        return response()->json([
            'message' => 'Status da categoria alterado.',
            'category' => $category,
        ]);
    }

    public function deleteCategory(int $id): JsonResponse
    {
        $this->checkAdmin();
        $category = Category::findOrFail($id);

        // Desvincula produtos vinculados a esta categoria
        Product::where('category_id', $id)->update(['category_id' => null]);

        $category->delete();

        return response()->json([
            'message' => 'Categoria excluída com sucesso e produtos desvinculados.',
        ]);
    }

    // --- PRODUTOS (Exclusivo Admin) ---

    public function getProducts(): JsonResponse
    {
        $this->checkAdmin();
        $products = Product::with('category')->orderBy('created_at', 'desc')->get();
        return response()->json($products);
    }

    public function saveProduct(Request $request, ?int $id = null): JsonResponse
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0.01',
            'is_promotion' => 'nullable|boolean',
            'is_combo' => 'nullable|boolean',
            'promotional_price' => 'nullable|numeric|min:0.01',
            'is_available' => 'nullable|boolean',
            'photo' => 'nullable|image|max:2048', // 2MB max
            'photo_path' => 'nullable|string|max:255',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $fileName = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/products'), $fileName);
            $photoPath = '/images/products/' . $fileName;
        }

        $data = [
            'category_id' => $validated['category_id'] ?? null,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'is_promotion' => $validated['is_promotion'] ?? false,
            'is_combo' => $validated['is_combo'] ?? false,
            'promotional_price' => ($validated['is_promotion'] ?? false) ? ($validated['promotional_price'] ?? null) : null,
            'is_available' => $validated['is_available'] ?? true,
        ];

        if ($photoPath) {
            $data['photo_path'] = $photoPath;
        } elseif ($request->filled('photo_path')) {
            $data['photo_path'] = $request->input('photo_path');
        }

        if ($id) {
            $product = Product::findOrFail($id);
            $product->update($data);
        } else {
            $product = Product::create($data);
        }

        return response()->json([
            'message' => 'Produto salvo com sucesso.',
            'product' => $product->load('category'),
        ]);
    }

    public function checkProductPendingOrders(int $id): JsonResponse
    {
        $this->checkAdmin();
        $hasPending = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.product_id', $id)
            ->whereIn('orders.status', ['recebido', 'em_preparacao', 'saiu_para_entrega'])
            ->exists();

        return response()->json([
            'has_pending_orders' => $hasPending,
        ]);
    }

    public function deleteProduct(int $id): JsonResponse
    {
        $this->checkAdmin();
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'message' => 'Item excluído com sucesso.',
        ]);
    }

    public function toggleProductAvailability(int $id): JsonResponse
    {
        $this->checkAdmin();
        $product = Product::findOrFail($id);
        $product->is_available = !$product->is_available;
        $product->save();

        return response()->json([
            'message' => 'Disponibilidade do produto atualizada.',
            'product' => $product,
        ]);
    }

    public function toggleProductPromotion(int $id): JsonResponse
    {
        $this->checkAdmin();
        $product = Product::findOrFail($id);
        $product->is_promotion = !$product->is_promotion;
        $product->save();

        return response()->json([
            'message' => 'Promoção atualizada.',
            'product' => $product,
        ]);
    }

    public function quickUpdateProductPrice(Request $request, int $id): JsonResponse
    {
        $this->checkAdmin();
        $validated = $request->validate([
            'price' => 'required|numeric|min:0.01',
        ]);

        $product = Product::findOrFail($id);
        $product->price = (float) $validated['price'];
        $product->save();

        return response()->json([
            'message' => "Preço de '{$product->name}' atualizado para R$ " . number_format($product->price, 2, ',', '.'),
            'product' => $product->load('category'),
        ]);
    }

    public function getMenuSettings(): JsonResponse
    {
        $this->checkAdmin();
        return response()->json([
            'pizza_sizes' => SystemSetting::getSetting('pizza_sizes', []),
            'pizza_borders' => SystemSetting::getSetting('pizza_borders', []),
            'monte_pastel' => SystemSetting::getSetting('monte_pastel', []),
            'monte_hamburguer' => SystemSetting::getSetting('monte_hamburguer', []),
            'combo_do_seu_jeito' => SystemSetting::getSetting('combo_do_seu_jeito', []),
            'store_whatsapp' => SystemSetting::getSetting('store_whatsapp', '(83) 99999-9999'),
        ]);
    }

    public function saveMenuSettings(Request $request): JsonResponse
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'pizza_sizes' => 'nullable|array',
            'pizza_borders' => 'nullable|array',
            'monte_pastel' => 'nullable|array',
            'monte_hamburguer' => 'nullable|array',
            'combo_do_seu_jeito' => 'nullable|array',
            'store_whatsapp' => 'nullable|string|max:30',
            'sync_pizzas' => 'nullable|boolean',
        ]);

        if (isset($validated['store_whatsapp'])) {
            SystemSetting::setSetting('store_whatsapp', trim($validated['store_whatsapp']), 'WhatsApp comercial da AKY Lanches');
        }

        if (isset($validated['pizza_sizes'])) {
            SystemSetting::setSetting('pizza_sizes', $validated['pizza_sizes'], 'Tamanhos e preços das pizzas');

            if (!empty($validated['sync_pizzas'])) {
                $small = collect($validated['pizza_sizes'])->firstWhere('id', 'pequena');
                if ($small && isset($small['price'])) {
                    $basePrice = (float) $small['price'];
                    Product::whereHas('category', function ($q) {
                        $q->whereIn('slug', ['pizzas-tradicionais', 'pizzas-doces']);
                    })->orWhere('name', 'LIKE', 'Pizza%')->update(['price' => $basePrice]);
                }
            }
        }

        if (isset($validated['pizza_borders'])) {
            SystemSetting::setSetting('pizza_borders', $validated['pizza_borders'], 'Bordas recheadas de pizza');
        }

        if (isset($validated['monte_pastel'])) {
            SystemSetting::setSetting('monte_pastel', $validated['monte_pastel'], 'Regras do Monte Seu Pastel');

            if (isset($validated['monte_pastel']['base_price'])) {
                $basePrice = (float) $validated['monte_pastel']['base_price'];
                Product::where('name', 'LIKE', '%Monte Seu Pastel%')->update(['price' => $basePrice]);
            }
        }

        if (isset($validated['monte_hamburguer'])) {
            SystemSetting::setSetting('monte_hamburguer', $validated['monte_hamburguer'], 'Regras e Ingredientes do Monte Seu Hambúrguer e Sanduíches');

            if (isset($validated['monte_hamburguer']['base_price'])) {
                $basePrice = (float) $validated['monte_hamburguer']['base_price'];
                Product::where('name', 'LIKE', '%Monte Seu Hamb%')->update(['price' => $basePrice]);
            }
        }

        if (isset($validated['combo_do_seu_jeito'])) {
            SystemSetting::setSetting('combo_do_seu_jeito', $validated['combo_do_seu_jeito'], 'Regras e Itens do Combo do Seu Jeito');

            if (isset($validated['combo_do_seu_jeito']['base_price'])) {
                $basePrice = (float) $validated['combo_do_seu_jeito']['base_price'];
                Product::where('name', 'LIKE', '%Combo do Seu Jeito%')->update([
                    'price' => $basePrice,
                    'promotional_price' => $basePrice,
                ]);
            }
        }

        return response()->json([
            'message' => 'Configurações e valores atualizados com sucesso.',
            'settings' => [
                'pizza_sizes' => SystemSetting::getSetting('pizza_sizes', []),
                'pizza_borders' => SystemSetting::getSetting('pizza_borders', []),
                'monte_pastel' => SystemSetting::getSetting('monte_pastel', []),
                'monte_hamburguer' => SystemSetting::getSetting('monte_hamburguer', []),
                'combo_do_seu_jeito' => SystemSetting::getSetting('combo_do_seu_jeito', []),
                'store_whatsapp' => SystemSetting::getSetting('store_whatsapp', '(83) 99999-9999'),
            ],
        ]);
    }

    public function syncPizzaPrices(Request $request): JsonResponse
    {
        $this->checkAdmin();
        $sizes = SystemSetting::getSetting('pizza_sizes', []);
        $small = collect($sizes)->firstWhere('id', 'pequena');
        $basePrice = $small ? (float) $small['price'] : 36.00;

        $updatedCount = Product::whereHas('category', function ($q) {
            $q->whereIn('slug', ['pizzas-tradicionais', 'pizzas-doces']);
        })->orWhere('name', 'LIKE', 'Pizza%')->update(['price' => $basePrice]);

        return response()->json([
            'message' => "Preço base de {$updatedCount} pizzas sincronizado para R$ " . number_format($basePrice, 2, ',', '.'),
        ]);
    }

    // --- ATENDENTES (Exclusivo Admin) ---

    public function getAttendants(): JsonResponse
    {
        $this->checkAdmin();
        $attendants = User::where('role', 'attendant')->orderBy('name', 'asc')->get();
        return response()->json($attendants);
    }

    public function createAttendant(Request $request): JsonResponse
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'document' => 'nullable|string|max:30',
            'password' => 'required|string|min:6',
        ]);

        $cleanName = trim(mb_strtoupper(preg_replace('/[^\p{L}\s]/u', '', $validated['name'])));
        if (empty($cleanName)) {
            return response()->json(['message' => 'Nome completo inválido. Use apenas letras e espaços.'], 422);
        }

        if (!empty($validated['document']) && strlen(preg_replace('/\D/', '', $validated['document'])) === 11) {
            if (!$this->isValidCpf($validated['document'])) {
                return response()->json(['message' => 'O CPF informado é inválido. Verifique os números digitados.'], 422);
            }
        }

        $attendant = User::create([
            'name' => $cleanName,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'document' => $validated['document'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'attendant',
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Atendente cadastrado com sucesso.',
            'attendant' => $attendant,
        ], 201);
    }

    // --- ADMINISTRADORES (Exclusivo Admin) ---

    public function getAdmins(): JsonResponse
    {
        $this->checkAdmin();
        $admins = User::where('role', 'admin')->orderBy('name', 'asc')->get();
        return response()->json($admins);
    }

    public function createAdmin(Request $request): JsonResponse
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'document' => 'nullable|string|max:30',
            'password' => 'required|string|min:6',
        ]);

        $cleanName = trim(mb_strtoupper(preg_replace('/[^\p{L}\s]/u', '', $validated['name'])));
        if (empty($cleanName)) {
            return response()->json(['message' => 'Nome completo inválido. Use apenas letras e espaços.'], 422);
        }

        if (!empty($validated['document']) && strlen(preg_replace('/\D/', '', $validated['document'])) === 11) {
            if (!$this->isValidCpf($validated['document'])) {
                return response()->json(['message' => 'O CPF informado é inválido. Verifique os números digitados.'], 422);
            }
        }

        $admin = User::create([
            'name' => $cleanName,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'document' => $validated['document'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Administrador cadastrado com sucesso.',
            'admin' => $admin,
        ], 201);
    }

    // --- COZINHA (Exclusivo Admin) ---

    public function getKitchenUsers(): JsonResponse
    {
        $this->checkAdmin();
        $users = User::where('role', 'kitchen')->orderBy('name', 'asc')->get();
        return response()->json($users);
    }

    public function createKitchenUser(Request $request): JsonResponse
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'document' => 'nullable|string|max:30',
            'password' => 'required|string|min:6',
        ]);

        $cleanName = trim(mb_strtoupper(preg_replace('/[^\p{L}\s]/u', '', $validated['name'])));
        if (empty($cleanName)) {
            return response()->json(['message' => 'Nome completo inválido. Use apenas letras e espaços.'], 422);
        }

        if (!empty($validated['document']) && strlen(preg_replace('/\D/', '', $validated['document'])) === 11) {
            if (!$this->isValidCpf($validated['document'])) {
                return response()->json(['message' => 'O CPF informado é inválido. Verifique os números digitados.'], 422);
            }
        }

        $kitchenUser = User::create([
            'name' => $cleanName,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'document' => $validated['document'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'kitchen',
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Usuário da Cozinha cadastrado com sucesso.',
            'user' => $kitchenUser,
        ], 201);
    }

    public function getActiveKitchenUsers(): JsonResponse
    {
        $this->checkStaff();
        $users = User::where('role', 'kitchen')
            ->where('is_active', true)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'email', 'phone']);
        return response()->json($users);
    }

    public function assignKitchenUser(Request $request, int $id): JsonResponse
    {
        $this->checkStaff();

        $validated = $request->validate([
            'kitchen_user_id' => 'nullable|exists:users,id',
            'status' => 'nullable|in:recebido,em_preparacao,saiu_para_entrega,concluido,cancelado',
        ]);

        $order = Order::findOrFail($id);

        if ($order->status === 'concluido') {
            return response()->json([
                'message' => 'Este pedido já foi concluído e não pode sofrer alterações de preparo.'
            ], 422);
        }

        $order->kitchen_user_id = $validated['kitchen_user_id'] ?? null;

        if (!empty($validated['status'])) {
            $order->status = $validated['status'];
        } elseif ($order->status === 'recebido') {
            $order->status = 'em_preparacao';
        }

        $order->save();

        return response()->json([
            'message' => 'Responsável pela cozinha atualizado com sucesso.',
            'order' => $order->load(['items.product', 'motoboy:id,name', 'kitchenUser:id,name']),
        ]);
    }

    // --- DADOS DA LANCHONETE & WHATSAPP (Exclusivo Admin) ---

    public function defaultStoreHours(): array
    {
        return [
            'general' => [
                'status' => 'auto', // 'auto', 'open', 'closed'
                'open_time' => '18:00',
                'close_time' => '23:30',
                'closed_message' => 'Estamos fechados no momento. Nosso horário de atendimento é das 18h00 às 23h30.',
            ],
            'delivery' => [
                'enabled' => true,
                'status' => 'auto', // 'auto', 'enabled', 'disabled'
                'open_time' => '18:00',
                'close_time' => '23:00',
                'disabled_message' => 'O serviço de entrega está pausado no momento. Atendimento disponível apenas para retirada no balcão!',
            ],
            'pickup' => [
                'enabled' => true,
                'status' => 'auto', // 'auto', 'enabled', 'disabled'
                'open_time' => '18:00',
                'close_time' => '23:30',
                'disabled_message' => 'A opção de retirada no balcão está temporariamente indisponível.',
            ],
            'only_pickup_mode' => false,
        ];
    }

    public static function calculateStoreStatus(?array $hours = null): array
    {
        if (empty($hours)) {
            $hours = (new self)->defaultStoreHours();
        }

        // Fuso horário oficial de Teixeira - PB / Paraíba
        $now = now()->setTimezone('America/Fortaleza');
        $currentTime = $now->format('H:i');

        $isWithinHours = function ($open, $close, $current) {
            if (empty($open) || empty($close)) {
                return true;
            }
            if ($open <= $close) {
                return $current >= $open && $current <= $close;
            } else {
                // Vira a meia-noite (ex: 18:00 às 01:00)
                return $current >= $open || $current <= $close;
            }
        };

        // 1. Status Geral da Loja
        $genConfig = $hours['general'] ?? [];
        $genStatus = $genConfig['status'] ?? 'auto';
        $genOpenTime = $genConfig['open_time'] ?? '18:00';
        $genCloseTime = $genConfig['close_time'] ?? '23:30';

        $isOpen = false;
        if ($genStatus === 'open') {
            $isOpen = true;
        } elseif ($genStatus === 'closed') {
            $isOpen = false;
        } else {
            $isOpen = $isWithinHours($genOpenTime, $genCloseTime, $currentTime);
        }

        // 2. Modo Só Retirada
        $onlyPickupMode = !empty($hours['only_pickup_mode']);

        // 3. Status de Entrega (Delivery)
        $delConfig = $hours['delivery'] ?? [];
        $delEnabled = $delConfig['enabled'] ?? true;
        $delStatus = $delConfig['status'] ?? 'auto';
        $delOpenTime = $delConfig['open_time'] ?? '18:00';
        $delCloseTime = $delConfig['close_time'] ?? '23:00';

        $canDelivery = false;
        if ($isOpen && !$onlyPickupMode && $delEnabled) {
            if ($delStatus === 'enabled') {
                $canDelivery = true;
            } elseif ($delStatus === 'disabled') {
                $canDelivery = false;
            } else {
                $canDelivery = $isWithinHours($delOpenTime, $delCloseTime, $currentTime);
            }
        }

        // 4. Status de Retirada (Pickup)
        $pickConfig = $hours['pickup'] ?? [];
        $pickEnabled = $pickConfig['enabled'] ?? true;
        $pickStatus = $pickConfig['status'] ?? 'auto';
        $pickOpenTime = $pickConfig['open_time'] ?? '18:00';
        $pickCloseTime = $pickConfig['close_time'] ?? '23:30';

        $canPickup = false;
        if ($isOpen && $pickEnabled) {
            if ($pickStatus === 'enabled') {
                $canPickup = true;
            } elseif ($pickStatus === 'disabled') {
                $canPickup = false;
            } else {
                $canPickup = $isWithinHours($pickOpenTime, $pickCloseTime, $currentTime);
            }
        }

        // Mensagens explicativas
        $noticeMessage = null;
        if (!$isOpen) {
            $noticeMessage = $genConfig['closed_message'] ?? "Estamos fechados no momento. Horário de atendimento: {$genOpenTime} às {$genCloseTime}.";
        } elseif ($onlyPickupMode) {
            $noticeMessage = 'Hoje estamos atendendo exclusivamente para Retirada no Balcão!';
        } elseif (!$canDelivery && $canPickup) {
            $noticeMessage = $delConfig['disabled_message'] ?? 'Entregas encerradas para este horário. Disponível apenas para retirada no balcão!';
        } elseif (!$canPickup && $canDelivery) {
            $noticeMessage = $pickConfig['disabled_message'] ?? 'Opção de retirada no balcão indisponível no momento.';
        } elseif (!$canDelivery && !$canPickup) {
            $noticeMessage = 'Todos os canais de pedido estão temporariamente indisponíveis.';
            $isOpen = false;
        }

        return [
            'is_open' => $isOpen,
            'can_delivery' => $canDelivery,
            'can_pickup' => $canPickup,
            'only_pickup_mode' => $onlyPickupMode,
            'current_time' => $currentTime,
            'general_status' => $genStatus,
            'general_label' => $isOpen ? 'Aberto' : 'Fechado',
            'delivery_label' => $canDelivery ? 'Entrega Ativa' : 'Entrega Fechada',
            'pickup_label' => $canPickup ? 'Retirada Ativa' : 'Retirada Fechada',
            'notice_message' => $noticeMessage,
        ];
    }

    public function getStoreInfo(): JsonResponse
    {
        $this->checkAdmin();
        $storeHours = SystemSetting::getSetting('store_hours', $this->defaultStoreHours());

        return response()->json([
            'store_whatsapp' => SystemSetting::getSetting('store_whatsapp', '(83) 99631-7878'),
            'require_motoboy_confirmation' => (bool) SystemSetting::getSetting('require_motoboy_confirmation', true),
            'store_info' => SystemSetting::getSetting('store_info', [
                'name' => 'AKY LANCHES',
                'slogan' => 'Cardápio Digital & Pedidos',
                'address' => 'Rua Principal, Centro - Teixeira - PB',
                'hours' => 'Todos os dias, das 18h00 às 23h30',
                'document' => '',
            ]),
            'store_hours' => $storeHours,
            'store_status' => self::calculateStoreStatus($storeHours),
            'whatsapp_messages' => SystemSetting::getSetting('whatsapp_messages', $this->defaultWhatsAppMessages()),
            'print_settings' => SystemSetting::getSetting('print_settings', $this->defaultPrintSettings()),
        ]);
    }

    public function saveStoreInfo(Request $request): JsonResponse
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'store_whatsapp' => 'nullable|string|max:30',
            'require_motoboy_confirmation' => 'nullable|boolean',
            'store_info' => 'nullable|array',
            'store_hours' => 'nullable|array',
            'whatsapp_messages' => 'nullable|array',
            'print_settings' => 'nullable|array',
        ]);

        if (isset($validated['store_whatsapp'])) {
            $cleaned = trim($validated['store_whatsapp']);
            SystemSetting::setSetting('store_whatsapp', $cleaned, 'WhatsApp comercial da AKY Lanches');

            // Atualiza globalmente pedidos antigos no banco de dados que estavam com telefone de teste (99) ou placeholder
            Order::where(function ($q) {
                $q->where('customer_phone', 'LIKE', '%(99)%')
                  ->orWhere('customer_phone', 'LIKE', '%999999999%')
                  ->orWhere('customer_phone', 'LIKE', '%99 99999%')
                  ->orWhere('customer_phone', 'LIKE', '%9999-9999%');
            })->update(['customer_phone' => $cleaned]);

            // Sincroniza telefone do admin caso estivesse em branco ou com 99
            $user = auth()->user();
            if ($user && ($user->phone === '(99) 99999-9999' || empty($user->phone))) {
                $user->update(['phone' => $cleaned]);
            }
        }

        if (array_key_exists('require_motoboy_confirmation', $validated)) {
            SystemSetting::setSetting('require_motoboy_confirmation', (bool) $validated['require_motoboy_confirmation'], 'Exigir que o motoboy confirme a entrega para poder concluir');
        }

        if (isset($validated['store_info'])) {
            SystemSetting::setSetting('store_info', $validated['store_info'], 'Dados gerais da AKY Lanches');
        }

        if (isset($validated['store_hours'])) {
            SystemSetting::setSetting('store_hours', $validated['store_hours'], 'Regras operacionais e horários de abertura, entrega e retirada');
        }

        if (isset($validated['whatsapp_messages'])) {
            SystemSetting::setSetting('whatsapp_messages', $validated['whatsapp_messages'], 'Modelos de mensagens do WhatsApp de pedidos');
        }

        if (isset($validated['print_settings'])) {
            SystemSetting::setSetting('print_settings', $validated['print_settings'], 'Configurações de impressão do cupom da cozinha');
        }

        $storeHours = SystemSetting::getSetting('store_hours', $this->defaultStoreHours());

        return response()->json([
            'message' => 'Configurações atualizadas globalmente com sucesso.',
            'store_whatsapp' => SystemSetting::getSetting('store_whatsapp', '(83) 99631-7878'),
            'require_motoboy_confirmation' => (bool) SystemSetting::getSetting('require_motoboy_confirmation', true),
            'store_info' => SystemSetting::getSetting('store_info', []),
            'store_hours' => $storeHours,
            'store_status' => self::calculateStoreStatus($storeHours),
            'whatsapp_messages' => SystemSetting::getSetting('whatsapp_messages', $this->defaultWhatsAppMessages()),
            'print_settings' => SystemSetting::getSetting('print_settings', $this->defaultPrintSettings()),
        ]);
    }

    public function defaultPrintSettings(): array
    {
        return [
            'paper_size' => '80mm',
            'custom_width_mm' => 80,
            'font_size' => '13px',
            'margin_mm' => 2,
            'show_prices' => false,
            'show_customer_phone' => true,
            'show_delivery_address' => true,
            'highlight_notes' => true,
            'show_cut_line' => true,
            'header_title' => 'AKY LANCHES • COZINHA',
            'custom_template' => "================================\n        {loja}\n     *** CUPOM DA COZINHA ***\n================================\nPEDIDO: #{codigo}\nDATA/HORA: {data_hora}\nTIPO: {tipo_pedido}\nCLIENTE: {cliente}\n{telefone}\n{endereco}\n--------------------------------\nITENS DA COMANDA:\n{itens_detalhados}\n--------------------------------\n{observacoes}\n{pagamento}\n{total}\n================================\n      {rodape}\n================================",
            'footer_text' => 'Bom apetite! Preparado com capricho.',
        ];
    }

    public function defaultWhatsAppMessages(): array
    {
        return [
            [
                'id' => 'msg_recebido',
                'title' => 'Pedido Recebido',
                'trigger' => 'recebido',
                'description' => 'Enviada quando o pedido acaba de ser confirmado no sistema.',
                'text' => "Olá, *{cliente}*! 👋\n\nRecebemos o seu pedido na *AKY LANCHES*!\n\n📋 *Pedido:* #{codigo}\n📍 *Status atual:* *Recebido e confirmado!*\n💰 *Total:* R$ {total}\n{pagamento}\n\n🔍 *Acompanhe em tempo real pelo link:*\n{link_rastreio}\n\nJá estamos organizando sua comanda. Em instantes começa a preparação! 🍔🍟",
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'id' => 'msg_em_preparacao',
                'title' => 'Pedido em Preparação',
                'trigger' => 'em_preparacao',
                'description' => 'Enviada quando a cozinha inicia o preparo dos itens.',
                'text' => "Olá, *{cliente}*! 👨‍🍳\n\nSeu pedido *#{codigo}* acabou de entrar na chapa e na cozinha da *AKY LANCHES*!\n\n🔥 Estamos montando e fritando tudo com o maior capricho.\n💰 *Total:* R$ {total}\n{pagamento}\n\n🔍 *Link de acompanhamento em tempo real:*\n{link_rastreio}\n\nLogo logo estará prontinho! 🥤🍔",
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'id' => 'msg_saiu_para_entrega',
                'title' => 'Saiu para Entrega (Com Motoboy)',
                'trigger' => 'saiu_para_entrega',
                'description' => 'Enviada quando o pedido sai para entrega com o motoboy.',
                'text' => "Partiu! 🛵 Olá, *{cliente}*!\n\nSeu pedido *#{codigo}* acabou de sair quentinho para entrega!\n\n{entregador}\n📍 *Destino:* {endereco}\n{pagamento}{troco}\n💰 *Total a pagar:* R$ {total}\n\n🔍 *Acompanhe a rota em tempo real:*\n{link_rastreio}\n\nFique atento à campainha ou interfone. Já já chega aí! 🍔🍟",
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'id' => 'msg_pronto_retirada',
                'title' => 'Pronto para Retirada no Balcão',
                'trigger' => 'pronto_retirada',
                'description' => 'Enviada para pedidos marcados para Retirar no Local quando estiverem prontos.',
                'text' => "Seu pedido está pronto! 🛍️ Olá, *{cliente}*!\n\nO pedido *#{codigo}* já está embalado e te esperando no balcão da *AKY LANCHES*!\n\n📍 *Local de Retirada:* {endereco}\n💰 *Total:* R$ {total}\n{pagamento}\n\nVenha buscar enquanto está quentinho e crocante! Te esperamos! 😊🍔",
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'id' => 'msg_concluido',
                'title' => 'Pedido Concluído / Agradecimento',
                'trigger' => 'concluido',
                'description' => 'Enviada após a entrega ou retirada confirmada.',
                'text' => "Pedido entregue com sucesso! 🎉\n\nOlá, *{cliente}*! Muito obrigado por escolher a *AKY LANCHES*!\n\nEsperamos que seu lanche esteja delicioso! Bom apetite! 😋🍔🥤\n\nQualquer dúvida ou feedback estamos sempre à disposição por aqui. Até a próxima!",
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'id' => 'msg_avaliacao',
                'title' => 'Solicitação de Avaliação do Pedido e Entrega',
                'trigger' => 'avaliacao',
                'description' => 'Enviada após entrega ou retirada para solicitar avaliação com estrelas e comentário.',
                'text' => "Olá, *{cliente}*! 👋🍔\n\nEsperamos que seu pedido da *AKY LANCHES* esteja uma delícia!\n\n⭐ *Poderia avaliar sua experiência em menos de 1 minuto?*\n\nSua opinião é fundamental para avaliarmos nosso lanche e nossa entrega:\n👉 {link_rastreio}\n\nBasta clicar no link acima para dar suas estrelas e deixar seu comentário. Muito obrigado! ❤️",
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'id' => 'msg_cancelado',
                'title' => 'Pedido Cancelado',
                'trigger' => 'cancelado',
                'description' => 'Enviada caso o pedido precise ser cancelado.',
                'text' => "Aviso sobre seu pedido: ⚠️\n\nOlá, *{cliente}*. Informamos que o pedido *#{codigo}* precisou ser cancelado.\n\nSe tiver qualquer dúvida ou se precisar refazer o pedido com alguma alteração, é só responder esta mensagem que nossa equipe te atende na hora! 🙏",
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'id' => 'msg_duvida_pedido',
                'title' => 'Dúvidas sobre o Pedido (Cliente / Rastreio)',
                'trigger' => 'duvida_pedido',
                'description' => 'Texto enviado pelo cliente via WhatsApp na tela de rastreamento ao tirar dúvidas sobre seu pedido.',
                'text' => "Olá, AKY Lanches! 👋🍔\n\nEstou com uma dúvida sobre o meu pedido *#{codigo}*:\n\n👤 *Cliente:* {cliente}\n📍 *Status atual:* {status}\n💰 *Total:* R$ {total}\n💳 *Pagamento:* {pagamento}\n📌 *Endereço/Retirada:* {endereco}\n\n🔍 *Link de Rastreio:* {link_rastreio}\n\nPoderiam me dar um suporte por favor?",
                'is_system' => true,
                'is_active' => true,
            ],
        ];
    }

    public function updateOrderPhone(Request $request, int $id): JsonResponse
    {
        $this->checkStaff();

        $validated = $request->validate([
            'customer_phone' => 'required|string|max:30',
        ]);

        $order = Order::findOrFail($id);
        $order->customer_phone = trim($validated['customer_phone']);
        $order->save();

        return response()->json([
            'message' => 'Telefone do pedido atualizado com sucesso!',
            'order' => $order->load(['items', 'motoboy:id,name']),
        ]);
    }

    public function updateUser(Request $request, int $id): JsonResponse
    {
        $this->checkAdmin();
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:30',
            'document' => 'nullable|string|max:30',
            'password' => 'nullable|string|min:6',
            'is_active' => 'nullable|boolean',
        ]);

        $cleanName = trim(mb_strtoupper(preg_replace('/[^\p{L}\s]/u', '', $validated['name'])));
        if (empty($cleanName)) {
            return response()->json(['message' => 'Nome completo inválido. Use apenas letras e espaços.'], 422);
        }

        if (!empty($validated['document']) && strlen(preg_replace('/\D/', '', $validated['document'])) === 11) {
            if (!$this->isValidCpf($validated['document'])) {
                return response()->json(['message' => 'O CPF informado é inválido. Verifique os números digitados.'], 422);
            }
        }

        $updateData = [
            'name' => $cleanName,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'document' => $validated['document'] ?? null,
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        if (isset($validated['is_active'])) {
            if ($user->role === 'admin' && !$validated['is_active'] && User::where('role', 'admin')->where('is_active', true)->count() <= 1) {
                return response()->json(['message' => 'Não é possível desativar o único administrador ativo.'], 422);
            }
            $updateData['is_active'] = $validated['is_active'];
        }

        $user->update($updateData);

        return response()->json([
            'message' => 'Dados do usuário atualizados com sucesso.',
            'user' => $user,
        ]);
    }

    public function toggleAttendantActive(int $id): JsonResponse
    {
        $this->checkAdmin();
        $user = User::findOrFail($id);

        if ($user->role === 'admin' && User::where('role', 'admin')->where('is_active', true)->count() <= 1) {
            return response()->json(['message' => 'Não é possível desativar o único administrador ativo.'], 422);
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json([
            'message' => 'Status do usuário atualizado.',
            'user' => $user,
        ]);
    }

    // --- ATRIBUIÇÃO DE MOTOBOY (Admin e Atendente) ---

    public function getMotoboys(): JsonResponse
    {
        $this->checkStaff();
        $motoboys = User::where('role', 'motoboy')->where('is_active', true)->orderBy('name', 'asc')->get(['id', 'name', 'email', 'phone', 'document']);
        return response()->json($motoboys);
    }

    public function getAllMotoboys(): JsonResponse
    {
        $this->checkAdmin();
        $motoboys = User::where('role', 'motoboy')->orderBy('created_at', 'desc')->get();
        return response()->json($motoboys);
    }

    public function createMotoboy(Request $request): JsonResponse
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'document' => 'nullable|string|max:30',
            'password' => 'required|string|min:6',
        ]);

        $cleanName = trim(mb_strtoupper(preg_replace('/[^\p{L}\s]/u', '', $validated['name'])));
        if (empty($cleanName)) {
            return response()->json(['message' => 'Nome completo inválido. Use apenas letras e espaços.'], 422);
        }

        if (!empty($validated['document']) && strlen(preg_replace('/\D/', '', $validated['document'])) === 11) {
            if (!$this->isValidCpf($validated['document'])) {
                return response()->json(['message' => 'O CPF informado é inválido. Verifique os números digitados.'], 422);
            }
        }

        $motoboy = User::create([
            'name' => $cleanName,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'document' => $validated['document'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'motoboy',
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Motoboy cadastrado com sucesso.',
            'motoboy' => $motoboy,
        ], 201);
    }

    public function assignMotoboy(Request $request, int $id): JsonResponse
    {
        $this->checkStaff();

        $validated = $request->validate([
            'motoboy_id' => 'nullable|exists:users,id',
            'status' => 'nullable|in:recebido,em_preparacao,saiu_para_entrega,concluido,cancelado',
        ]);

        $order = Order::findOrFail($id);

        if ($order->status === 'concluido') {
            return response()->json([
                'message' => 'Este pedido já foi concluído e seu entregador não pode ser alterado.'
            ], 422);
        }

        $order->motoboy_id = $validated['motoboy_id'] ?? null;
        
        if (!empty($validated['status'])) {
            $isPickup = $order->delivery_address && str_contains($order->delivery_address, 'RETIRADA');

        // Validação 1: Pedido de Entrega saindo para entrega precisa de motoboy
        if ($validated['status'] === 'saiu_para_entrega' && !$isPickup && empty($order->motoboy_id)) {
            return response()->json([
                'message' => 'Para alterar o status para "Saiu para Entrega", é obrigatório atribuir um entregador (motoboy) ao pedido de entrega.'
            ], 422);
        }

        // Validação 2: Se a regra estiver ativa, pedidos de entrega só podem ser concluídos pelo próprio entregador
        $requireConfirmation = (bool) SystemSetting::getSetting('require_motoboy_confirmation', true);
        if ($validated['status'] === 'concluido' && !$isPickup && $requireConfirmation) {
            $user = Auth::user();
            if ($user && $user->role !== 'motoboy') {
                return response()->json([
                    'message' => 'Regra ativa: Este pedido é para entrega e deve ser confirmado diretamente pelo entregador no aplicativo dele para ser concluído. (Essa regra pode ser desativada pelo Administrador nas Configurações).'
                ], 422);
            }
        }

        $order->status = $validated['status'];
        }

        $order->save();

        return response()->json([
            'message' => 'Entrega atribuída com sucesso.',
            'order' => $order->load(['items', 'motoboy:id,name']),
        ]);
    }

    // --- PAINEL DO MOTOBOY ---

    public function getMotoboyOrders(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'motoboy') {
            abort(403, 'Acesso exclusivo para entregadores.');
        }

        $orders = Order::where('motoboy_id', $user->id)
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    public function completeMotoboyDelivery(Request $request, int $id): JsonResponse
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'motoboy') {
            abort(403, 'Acesso exclusivo para entregadores.');
        }

        $order = Order::where('id', $id)->where('motoboy_id', $user->id)->firstOrFail();
        
        $order->motoboy_confirmed_at = now();
        
        // Se o cliente já confirmou o recebimento, ou se a regra não for estrita, passa para concluido
        // Caso contrário, motoboy já confirmou sua parte!
        $requireConfirmation = (bool) SystemSetting::getSetting('require_motoboy_confirmation', true);
        
        if (!empty($order->customer_confirmed_at) || !$requireConfirmation) {
            $order->status = 'concluido';
        } else {
            // Permanece em saiu_para_entrega com motoboy_confirmed_at preenchido até o cliente ou atendente desempatar/confirmar
            // Ou se desejar que o motoboy conclua caso a regra seja dele:
            $order->status = 'concluido';
        }
        $order->save();

        return response()->json([
            'message' => 'Entrega confirmada pelo entregador com sucesso!',
            'order' => $order,
        ]);
    }

    // --- PAINEL KDS DA COZINHA (TV / Monitor) ---

    public function getKitchenOrders(Request $request): JsonResponse
    {
        $this->checkKitchenOrStaff();

        // Pedidos em preparação (prioridade na TV KDS) ordenados por FIFO (mais antigos primeiro)
        $query = Order::where('status', 'em_preparacao')
            ->with(['items.product', 'kitchenUser:id,name'])
            ->orderBy('created_at', 'asc');

        if ($request->filled('kitchen_user_id')) {
            if ($request->kitchen_user_id === 'unassigned') {
                $query->whereNull('kitchen_user_id');
            } else {
                $query->where('kitchen_user_id', $request->kitchen_user_id);
            }
        }

        $orders = $query->get();

        // Últimos 6 pedidos prontos recentemente para acompanhamento na TV
        $recentReady = Order::whereIn('status', ['saiu_para_entrega', 'concluido'])
            ->with(['items.product', 'kitchenUser:id,name'])
            ->where('updated_at', '>=', now()->subHours(2))
            ->orderBy('updated_at', 'desc')
            ->limit(6)
            ->get();

        // Operadores de cozinha ativos para filtros
        $kitchenStaff = User::where('role', 'kitchen')
            ->where('is_active', true)
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);

        return response()->json([
            'preparing' => $orders,
            'recent_ready' => $recentReady,
            'kitchen_staff' => $kitchenStaff,
            'count' => $orders->count(),
        ]);
    }

    public function markKitchenOrderReady(Request $request, int $id): JsonResponse
    {
        $this->checkKitchenOrStaff();

        $order = Order::findOrFail($id);
        
        if ($order->status === 'saiu_para_entrega' || $order->status === 'concluido') {
            return response()->json([
                'message' => 'Este pedido já foi finalizado na cozinha.',
                'order' => $order->load('items'),
            ]);
        }

        $order->status = 'saiu_para_entrega';
        $order->save();

        return response()->json([
            'message' => 'Pedido marcado como pronto pela cozinha!',
            'order' => $order->load('items'),
        ]);
    }

    private function isValidCpf(?string $cpf): bool
    {
        if (empty($cpf)) {
            return true;
        }

        $digits = preg_replace('/\D/', '', $cpf);
        if (strlen($digits) !== 11) {
            return false;
        }

        if (preg_match('/^(\d)\1{10}$/', $digits)) {
            return false;
        }

        // 1º Dígito Verificador
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int) $digits[$i] * (10 - $i);
        }
        $rest = ($sum * 10) % 11;
        $d1 = ($rest === 10 || $rest === 11) ? 0 : $rest;
        if ($d1 !== (int) $digits[9]) {
            return false;
        }

        // 2º Dígito Verificador
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += (int) $digits[$i] * (11 - $i);
        }
        $rest = ($sum * 10) % 11;
        $d2 = ($rest === 10 || $rest === 11) ? 0 : $rest;
        return $d2 === (int) $digits[10];
    }
}

