<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MenuController;
use Illuminate\Support\Facades\Route;

// --- API PÚBLICA ---
Route::prefix('api')->group(function () {
    Route::get('/menu', [MenuController::class, 'index']);
    Route::post('/orders', [MenuController::class, 'createOrder']);
    Route::get('/orders/{orderNumber}', [MenuController::class, 'trackOrder']);

    // Autenticação
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Gestão Administrativa e Atendimento
    Route::prefix('admin')->group(function () {
        Route::get('/orders', [AdminController::class, 'getOrders']);
        Route::patch('/orders/{id}/status', [AdminController::class, 'updateOrderStatus']);
        Route::patch('/orders/{id}/phone', [AdminController::class, 'updateOrderPhone']);
        Route::delete('/orders/{id}', [AdminController::class, 'deleteOrder']);

        Route::get('/categories', [AdminController::class, 'getCategories']);
        Route::post('/categories', [AdminController::class, 'saveCategory']);
        Route::put('/categories/{id}', [AdminController::class, 'saveCategory']);
        Route::delete('/categories/{id}', [AdminController::class, 'deleteCategory']);
        Route::patch('/categories/{id}/toggle', [AdminController::class, 'toggleCategoryActive']);

        Route::get('/products', [AdminController::class, 'getProducts']);
        Route::post('/products', [AdminController::class, 'saveProduct']);
        Route::post('/products/{id}', [AdminController::class, 'saveProduct']);
        Route::delete('/products/{id}', [AdminController::class, 'deleteProduct']);
        Route::get('/products/{id}/pending-orders-check', [AdminController::class, 'checkProductPendingOrders']);
        Route::patch('/products/{id}/toggle-availability', [AdminController::class, 'toggleProductAvailability']);
        Route::patch('/products/{id}/toggle-promotion', [AdminController::class, 'toggleProductPromotion']);
        Route::patch('/products/{id}/quick-price', [AdminController::class, 'quickUpdateProductPrice']);

        // Gestão de Configurações Dinâmicas dos Montadores e Preços do Sistema
        Route::get('/menu-settings', [AdminController::class, 'getMenuSettings']);
        Route::post('/menu-settings', [AdminController::class, 'saveMenuSettings']);
        Route::post('/sync-pizza-prices', [AdminController::class, 'syncPizzaPrices']);

        // Gestão de Atendentes e Administradores
        Route::get('/attendants', [AdminController::class, 'getAttendants']);
        Route::post('/attendants', [AdminController::class, 'createAttendant']);
        Route::patch('/attendants/{id}/toggle', [AdminController::class, 'toggleAttendantActive']);
        Route::get('/admins', [AdminController::class, 'getAdmins']);
        Route::post('/admins', [AdminController::class, 'createAdmin']);
        Route::get('/store-info', [AdminController::class, 'getStoreInfo']);
        Route::post('/store-info', [AdminController::class, 'saveStoreInfo']);
        Route::put('/users/{id}', [AdminController::class, 'updateUser']);

        // Gestão e Atribuição de Motoboys
        Route::get('/motoboys', [AdminController::class, 'getMotoboys']);
        Route::get('/all-motoboys', [AdminController::class, 'getAllMotoboys']);
        Route::post('/motoboys', [AdminController::class, 'createMotoboy']);
        Route::patch('/orders/{id}/assign-motoboy', [AdminController::class, 'assignMotoboy']);
    });

    // Painel do Entregador (Motoboy)
    Route::prefix('motoboy')->group(function () {
        Route::get('/orders', [AdminController::class, 'getMotoboyOrders']);
        Route::patch('/orders/{id}/complete', [AdminController::class, 'completeMotoboyDelivery']);
    });
});

// Single Page Application (SPA) - Renderiza a view principal para todas as telas
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '^(?!api).*$');
