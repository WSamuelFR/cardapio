<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
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

    // --- PEDIDOS (Admin e Atendente) ---

    public function getOrders(Request $request): JsonResponse
    {
        $this->checkStaff();

        $query = Order::with(['items', 'motoboy:id,name'])->orderBy('created_at', 'desc');

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

        $order->status = $validated['status'];
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
        $order->status = 'concluido';
        $order->save();

        return response()->json([
            'message' => 'Entrega confirmada como concluída!',
            'order' => $order,
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

