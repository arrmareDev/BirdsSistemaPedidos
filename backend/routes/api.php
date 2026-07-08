<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ExtraController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\CajaController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SistemaController;
use App\Http\Controllers\Api\DespachoController;
use App\Http\Controllers\Api\DespachoWebhookController;
use App\Http\Controllers\Api\DeliveryZoneController;

// ══════════════════════════════════════════════════════════
// ── Públicas — sin auth ────────────────────────────────────
// ══════════════════════════════════════════════════════════
Route::prefix('v1')->group(function () {

    Route::post('auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1');

    Route::get('categories',    [CategoryController::class, 'index']);
    Route::get('products',      [ProductController::class, 'index']);
    Route::get('products/{id}', [ProductController::class, 'show'])
        ->where('id', '[0-9]+');

    Route::post('orders',        [OrderController::class, 'store']);
    Route::post('orders/search', [OrderController::class, 'search']);

    Route::get('orders/{id}/status', [OrderController::class, 'status'])
        ->where('id', '[0-9]+');
    Route::get('orders/{id}/track',  [OrderController::class, 'track'])
        ->where('id', '[0-9]+');

    Route::get('delivery-zones/detectar', [DeliveryZoneController::class, 'detectar']);
    Route::get('delivery-zones', [DeliveryZoneController::class, 'index']);

    Route::post('webhooks/despacho', [DespachoWebhookController::class, 'handle']);
});

// ══════════════════════════════════════════════════════════
// ── Protegidas — requieren auth:sanctum ─────────────────────
// ══════════════════════════════════════════════════════════
Route::prefix('v1/admin')
    ->middleware(['auth:sanctum', 'throttle:120,1'])
    ->group(function () {

        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me',      [AuthController::class, 'me']);

        // ══ CATÁLOGO ══════════════════════════════════════════

        Route::middleware(['role:cajero,admin,sistema,super_admin', 'permission:catalog'])->group(function () {
            Route::get('products',   [ProductController::class, 'adminIndex']);
            Route::get('categories', [CategoryController::class, 'adminIndex']);
        });

        Route::middleware(['role:admin,sistema,super_admin', 'permission:catalog'])->group(function () {
            Route::post('categories',           [CategoryController::class, 'store']);
            Route::put('categories/{id}',       [CategoryController::class, 'update'])
                ->where('id', '[0-9]+');
            Route::delete('categories/{id}',    [CategoryController::class, 'destroy'])
                ->where('id', '[0-9]+');

            Route::post('products',             [ProductController::class, 'store']);
            Route::post('products/{id}/update', [ProductController::class, 'update'])
                ->where('id', '[0-9]+');
            Route::delete('products/{id}',      [ProductController::class, 'destroy'])
                ->where('id', '[0-9]+');
            Route::post('products/{id}/toggle', [ProductController::class, 'toggle'])
                ->where('id', '[0-9]+');

            Route::get('extras',           [ExtraController::class, 'index']);
            Route::post('extras',          [ExtraController::class, 'store']);
            Route::put('extras/{id}',      [ExtraController::class, 'update'])
                ->where('id', '[0-9]+');
            Route::delete('extras/{id}',   [ExtraController::class, 'destroy'])
                ->where('id', '[0-9]+');
        });

        // ══ PEDIDOS ═══════════════════════════════════════════

        Route::middleware(['role:cajero,admin,sistema,super_admin', 'permission:orders'])->group(function () {
            Route::get('orders',               [OrderController::class, 'index']);
            Route::get('orders/{id}',          [OrderController::class, 'show'])
                ->where('id', '[0-9]+');
            Route::patch('orders/{id}/status', [OrderController::class, 'updateStatus'])
                ->where('id', '[0-9]+');
        });

        Route::middleware(['role:cajero,admin,super_admin', 'permission:orders'])->group(function () {
            Route::post('orders', [OrderController::class, 'adminStore']);
        });

        Route::middleware(['role:admin,sistema,super_admin', 'permission:orders'])->group(function () {
            Route::delete('orders/{id}', [OrderController::class, 'destroy'])
                ->where('id', '[0-9]+');
        });

        // ══ DESPACHOS — ligados al permiso de "orders" ═══════════

        Route::middleware(['role:cajero,admin,sistema,super_admin', 'permission:orders'])->group(function () {
            Route::post('despachos/solicitar', [DespachoController::class, 'solicitar']);
            Route::get('despachos/{order_id}/estado', [DespachoController::class, 'estado'])
                ->where('order_id', '[0-9]+');
        });

        Route::middleware(['role:admin,sistema,super_admin', 'permission:orders'])->group(function () {
            Route::post('despachos/{order_id}/cancelar', [DespachoController::class, 'cancelar'])
                ->where('order_id', '[0-9]+');
        });

        // ══ CAJA ══════════════════════════════════════════════

        Route::middleware(['role:cajero,admin,sistema,super_admin', 'permission:caja'])->group(function () {
            Route::get('caja/hoy', [CajaController::class, 'hoy']);
            Route::post('caja/abrir',      [CajaController::class, 'abrir']);
            Route::post('caja/movimiento', [CajaController::class, 'movimiento']);
            Route::post('caja/cerrar',     [CajaController::class, 'cerrar']);
        });

        // ══ CLIENTES ══════════════════════════════════════════

        Route::middleware(['role:cajero,admin,sistema,super_admin', 'permission:clients'])->group(function () {
            Route::get('clients',      [ClientController::class, 'index']);
            Route::get('clients/{id}', [ClientController::class, 'show'])
                ->where('id', '[0-9]+');
        });

        // ══ REPORTES ══════════════════════════════════════════

        Route::middleware(['role:admin,sistema,super_admin', 'permission:reports'])->group(function () {
            Route::get('reports/sales',          [ReportController::class, 'sales']);
            Route::get('reports/customizations', [ReportController::class, 'customizations']);
        });

        // ══ USUARIOS ══════════════════════════════════════════

        Route::middleware(['role:admin,sistema,super_admin', 'permission:users'])->group(function () {
            Route::get('users',         [UserController::class, 'index']);
            Route::get('users/{id}',    [UserController::class, 'show'])
                ->where('id', '[0-9]+');
            Route::post('users',        [UserController::class, 'store']);
            Route::put('users/{id}',    [UserController::class, 'update'])
                ->where('id', '[0-9]+');
            Route::delete('users/{id}', [UserController::class, 'destroy'])
                ->where('id', '[0-9]+');
        });

        Route::middleware('role:sistema,super_admin')->group(function () {
            Route::post('users/{id}/reset', [UserController::class, 'resetPassword'])
                ->where('id', '[0-9]+');
        });

        // ══ SISTEMA ════════════════════════════════════════════

        Route::middleware(['role:sistema,super_admin', 'permission:sistema'])->group(function () {
            Route::get('sistema/dashboard', [SistemaController::class, 'dashboard']);
            Route::get('sistema/config',    [SistemaController::class, 'getConfig']);
            Route::put('sistema/config',    [SistemaController::class, 'updateConfig']);
            Route::post('sistema/cobrar',   [SistemaController::class, 'marcarCobrado']);
        });

        Route::middleware('role:cajero,admin,sistema,super_admin')->group(function () {
            Route::get(
                'sistema/comisiones-pendientes',
                [SistemaController::class, 'comisionesPendientesCaja']
            );
        });

        // ══ ZONAS DE DELIVERY — ligado al permiso de "catalog" ════

        Route::middleware(['role:admin,sistema,super_admin', 'permission:catalog'])->group(function () {
            Route::get('delivery-zones', [DeliveryZoneController::class, 'adminIndex']);
            Route::post('delivery-zones', [DeliveryZoneController::class, 'store']);
            Route::post('delivery-zones/reorder', [DeliveryZoneController::class, 'reorder']);
            Route::put('delivery-zones/{id}', [DeliveryZoneController::class, 'update'])
                ->where('id', '[0-9]+');
            Route::delete('delivery-zones/{id}', [DeliveryZoneController::class, 'destroy'])
                ->where('id', '[0-9]+');
            Route::patch('delivery-zones/{id}/toggle', [DeliveryZoneController::class, 'toggle'])
                ->where('id', '[0-9]+');
        });
    });
