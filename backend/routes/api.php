<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\MovimientoStockController;
use App\Http\Controllers\Api\InventarioReporteController;
use App\Http\Controllers\Api\ExtraController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\CajaController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PushSubscriptionController;
use App\Http\Controllers\Api\SistemaController;
use App\Http\Controllers\Api\DespachoController;
use App\Http\Controllers\Api\DespachoWebhookController;
use App\Http\Controllers\Api\DeliveryZoneController;
use App\Http\Controllers\Api\SeccionTipoController;
use App\Http\Controllers\Api\ProveedorController;

// ══════════════════════════════════════════════════════════
// ── Públicas — sin auth ────────────────────────────────────
// ══════════════════════════════════════════════════════════
Route::prefix('v1')->group(function () {

    Route::post('auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1');

    Route::get('categories',    [CategoryController::class, 'index']);
    Route::get('products',      [ProductController::class, 'index']);
    Route::get('products/{slug}', [ProductController::class, 'show']);

    Route::post('orders',        [OrderController::class, 'store']);
    Route::post('orders/search', [OrderController::class, 'search']);

    Route::get('orders/{id}/status', [OrderController::class, 'status'])
        ->where('id', '[0-9]+');
    Route::get('orders/{id}/track',  [OrderController::class, 'track'])
        ->where('id', '[0-9]+');

    Route::get('delivery-zones/detectar', [DeliveryZoneController::class, 'detectar']);
    Route::get('delivery-zones', [DeliveryZoneController::class, 'index']);

    Route::get('branding', [SistemaController::class, 'getBranding']);
    Route::get('vapid-public-key', [PushSubscriptionController::class, 'publicKey']);
    Route::get('pedido-config', [SistemaController::class, 'getPedidoConfig']);

    Route::post('webhooks/despacho', [DespachoWebhookController::class, 'handle']);
});

// ══════════════════════════════════════════════════════════
// ── Protegidas — requieren auth:sanctum ─────────────────────
// ══════════════════════════════════════════════════════════
Route::prefix('v1/admin')
    ->middleware(['auth:sanctum', 'throttle:120,1', 'require-password-change'])
    ->group(function () {

        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me',      [AuthController::class, 'me']);
        Route::put('auth/password', [AuthController::class, 'changePassword']);

        // Cualquier rol logueado puede ver el directorio de proveedores —
        // solo crear/editar/borrar queda restringido a sistema, más abajo.
        Route::get('proveedores', [ProveedorController::class, 'adminIndex']);
        Route::post('proveedores/{id}/clic', [ProveedorController::class, 'registrarClic'])
            ->where('id', '[0-9]+');

        Route::post('push/subscribe', [PushSubscriptionController::class, 'subscribe']);
        Route::post('push/unsubscribe', [PushSubscriptionController::class, 'unsubscribe']);

        // ══ CATÁLOGO ══════════════════════════════════════════

        // Lectura: admin/sistema/contador (ven el módulo) + atencion
        // (necesita leer productos para armar pedidos, sin ver el módulo completo)
        Route::middleware(['role:admin,sistema,contador,atencion', 'permission:catalog|orders'])->group(function () {
            Route::get('products',   [ProductController::class, 'adminIndex']);
            Route::get('categories', [CategoryController::class, 'adminIndex']);
            Route::get('products/{id}/movimientos-stock', [MovimientoStockController::class, 'index'])
                ->where('id', '[0-9]+');
            Route::get('reportes/inventario/pdf',   [InventarioReporteController::class, 'pdf']);
            Route::get('reportes/inventario/excel', [InventarioReporteController::class, 'excel']);
        });

        // Escritura: solo admin/sistema — contador NO gestiona catálogo
        Route::middleware(['role:admin,sistema', 'permission:catalog'])->group(function () {
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
            Route::post('products/{productId}/options/{optionId}/image', [ProductController::class, 'uploadOptionImage'])
                ->where(['productId' => '[0-9]+', 'optionId' => '[0-9]+']);
            Route::delete('products/{productId}/options/{optionId}/image', [ProductController::class, 'deleteOptionImage'])
                ->where(['productId' => '[0-9]+', 'optionId' => '[0-9]+']);

            Route::post('products/{id}/images', [ProductController::class, 'uploadImages'])
                ->where('id', '[0-9]+');
            Route::delete('products/{productId}/images/{imageId}', [ProductController::class, 'deleteImage'])
                ->where(['productId' => '[0-9]+', 'imageId' => '[0-9]+']);
            Route::post('products/{id}/images/reorder', [ProductController::class, 'reorderImages'])
                ->where('id', '[0-9]+');

            Route::post('products/{id}/reponer-stock', [MovimientoStockController::class, 'reponer'])
                ->where('id', '[0-9]+');
            Route::post('products/{id}/ajustar-stock', [MovimientoStockController::class, 'ajustar'])
                ->where('id', '[0-9]+');

            Route::get('extras',           [ExtraController::class, 'index']);
            Route::post('extras',          [ExtraController::class, 'store']);
            Route::put('extras/{id}',      [ExtraController::class, 'update'])
                ->where('id', '[0-9]+');
            Route::delete('extras/{id}',   [ExtraController::class, 'destroy'])
                ->where('id', '[0-9]+');
        });

        // ══ PEDIDOS ═══════════════════════════════════════════

        // Lectura: todos los roles con acceso a "orders", incluido Salón (solo lectura)
        Route::middleware(['role:admin,sistema,contador,atencion,salon', 'permission:orders'])->group(function () {
            Route::get('orders',      [OrderController::class, 'index']);
            Route::get('orders/{id}', [OrderController::class, 'show'])
                ->where('id', '[0-9]+');
        });

        Route::middleware(['role:admin,sistema,contador,atencion', 'permission:orders'])->group(function () {
            Route::post('orders',              [OrderController::class, 'adminStore']);
            Route::patch('orders/{id}/status', [OrderController::class, 'updateStatus'])   // ← esta falta
                ->where('id', '[0-9]+');
            Route::put('orders/{id}/items',    [OrderController::class, 'updateItems'])
                ->where('id', '[0-9]+');
            Route::patch('orders/{id}/cobrar', [OrderController::class, 'cobrar'])
                ->where('id', '[0-9]+');
        });

        Route::middleware(['role:admin,sistema,atencion', 'permission:orders'])->group(function () {
            Route::delete('orders/{id}', [OrderController::class, 'destroy'])
                ->where('id', '[0-9]+');

            Route::get('orders/trashed', [OrderController::class, 'trashed']);
            Route::post('orders/{id}/restore', [OrderController::class, 'restore'])
                ->where('id', '[0-9]+');

            Route::delete('orders/{id}/force', [OrderController::class, 'forceDestroy'])
                ->where('id', '[0-9]+');
        });

        // ══ DESPACHOS — vía Delivery Central ═══════════════════

        Route::middleware(['role:admin,sistema,contador', 'permission:orders'])->group(function () {
            Route::post('despachos/solicitar', [DespachoController::class, 'solicitar']);
            Route::get('despachos/{order_id}/estado', [DespachoController::class, 'estado'])
                ->where('order_id', '[0-9]+');
        });

        Route::middleware(['role:admin,sistema', 'permission:orders'])->group(function () {
            Route::post('despachos/{order_id}/cancelar', [DespachoController::class, 'cancelar'])
                ->where('order_id', '[0-9]+');
        });

        // ══ CAJA ══════════════════════════════════════════════

        Route::middleware(['role:admin,sistema,contador', 'permission:caja'])->group(function () {
            Route::get('caja/hoy', [CajaController::class, 'hoy']);
            Route::get('caja/historial', [CajaController::class, 'historial']);
            Route::get('caja/{id}/movimientos', [CajaController::class, 'movimientos'])
                ->where('id', '[0-9]+');
            Route::post('caja/abrir',      [CajaController::class, 'abrir']);
            Route::post('caja/movimiento', [CajaController::class, 'movimiento']);
            Route::post('caja/movimiento/{id}/anular', [CajaController::class, 'anular'])
                ->where('id', '[0-9]+');
            Route::post('caja/cerrar',     [CajaController::class, 'cerrar']);
        });

        // ══ CLIENTES ══════════════════════════════════════════

        Route::middleware(['role:admin,sistema,contador', 'permission:clients'])->group(function () {
            Route::get('clients',      [ClientController::class, 'index']);
            Route::get('clients/{id}', [ClientController::class, 'show'])
                ->where('id', '[0-9]+');
        });

        // ══ REPORTES ══════════════════════════════════════════

        Route::middleware(['role:admin,sistema,contador', 'permission:reports'])->group(function () {
            Route::get('reports/sales',          [ReportController::class, 'sales']);
            Route::get('reports/customizations', [ReportController::class, 'customizations']);
            Route::get('reports/historico',      [ReportController::class, 'historico']);
        });

        // ══ USUARIOS — solo admin/sistema ═══════════════════════

        Route::middleware(['role:admin,sistema', 'permission:users'])->group(function () {
            Route::get('users',         [UserController::class, 'index']);
            Route::get('users/{id}',    [UserController::class, 'show'])
                ->where('id', '[0-9]+');
            Route::post('users',        [UserController::class, 'store']);
            Route::put('users/{id}',    [UserController::class, 'update'])
                ->where('id', '[0-9]+');
            Route::delete('users/{id}', [UserController::class, 'destroy'])
                ->where('id', '[0-9]+');
        });

        Route::middleware('role:admin,sistema')->group(function () {
            Route::post('users/{id}/reset', [UserController::class, 'resetPassword'])
                ->where('id', '[0-9]+');
        });

        // ══ SISTEMA (módulo financiero/comisiones) ══════════════
        // Ajustes es 100% exclusivo del rol sistema — antes admin y
        // contador también podían entrar a ver el dashboard.
        Route::middleware(['role:sistema', 'permission:sistema'])->group(function () {
            Route::get('sistema/dashboard', [SistemaController::class, 'dashboard']);
        });

        // Cambiar la tarifa de comisión y marcar cobros: exclusivo del
        // rol sistema — es la relación comercial entre tú y el negocio,
        // el admin del negocio no debe poder tocar esto ni por API directa.
        Route::middleware('role:sistema')->group(function () {
            Route::get('sistema/config', [SistemaController::class, 'getConfig']);
            Route::put('sistema/config', [SistemaController::class, 'updateConfig']);
            Route::post('sistema/cobrar', [SistemaController::class, 'marcarCobrado']);

            // Directorio de proveedores aliados — lo curamos nosotros,
            // no cada negocio individual. Cualquier rol puede consultarlo
            // (arriba), pero solo sistema puede crear/editar/borrar.
            Route::post('proveedores', [ProveedorController::class, 'store']);
            Route::post('proveedores/reorder', [ProveedorController::class, 'reorder']);
            Route::put('proveedores/{id}', [ProveedorController::class, 'update'])
                ->where('id', '[0-9]+');
            // También por POST — permite mandar archivos (logo) al editar,
            // sin depender de que el navegador spoofee el método PUT.
            Route::post('proveedores/{id}', [ProveedorController::class, 'update'])
                ->where('id', '[0-9]+');
            Route::delete('proveedores/{id}', [ProveedorController::class, 'destroy'])
                ->where('id', '[0-9]+');
        });

        // Antes admin también podía editar branding/catálogo desde
        // aquí — ahora Ajustes completo es exclusivo de sistema.
        Route::middleware('role:sistema')->group(function () {
            Route::post('sistema/branding', [SistemaController::class, 'updateBranding']);
            Route::post('sistema/pedido-config', [SistemaController::class, 'updatePedidoConfig']);
        });

        Route::middleware('role:admin,sistema,contador')->group(function () {
            Route::get(
                'sistema/comisiones-pendientes',
                [SistemaController::class, 'comisionesPendientesCaja']
            );
        });

        // ══ ZONAS DE DELIVERY — solo sistema ════════════════════

        Route::middleware(['role:sistema', 'permission:catalog'])->group(function () {
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

        // ══ TIPOS DE SECCIÓN DE PERSONALIZACIÓN — solo admin/sistema ═══
        Route::middleware(['role:admin,sistema', 'permission:catalog'])->group(function () {
            Route::get('seccion-tipos', [SeccionTipoController::class, 'adminIndex']);
            Route::post('seccion-tipos', [SeccionTipoController::class, 'store']);
            Route::post('seccion-tipos/reorder', [SeccionTipoController::class, 'reorder']);
            Route::put('seccion-tipos/{id}', [SeccionTipoController::class, 'update'])
                ->where('id', '[0-9]+');
            Route::delete('seccion-tipos/{id}', [SeccionTipoController::class, 'destroy'])
                ->where('id', '[0-9]+');
        });
    });
