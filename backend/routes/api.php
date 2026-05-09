<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AdminSolicitudRegistroController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ClienteController;
use App\Http\Controllers\Api\V1\ServicioController;
use App\Http\Controllers\Api\V1\TipoClienteController;
use App\Http\Controllers\Api\V1\TipoDocumentoIdentidadController;
use App\Http\Controllers\Api\V1\TipoEmpresaController;
use App\Http\Controllers\Api\V1\TipoEventoFacturacionController;
use App\Http\Controllers\Api\V1\TipoFacturaController;
use App\Http\Controllers\Api\V1\TipoInventarioMovimientoController;
use App\Http\Controllers\Api\V1\TipoLocalizacionClienteController;
use App\Http\Controllers\Api\V1\TipoRectificacionController;
use App\Http\Controllers\Api\V1\TipoRegistroFacturacionController;
use App\Http\Controllers\Api\V1\EstadoFacturaController;
use App\Http\Controllers\Api\V1\FacturaController;
use App\Http\Controllers\Api\V1\InformeController;
use App\Http\Controllers\Api\V1\InventarioCategoriaController;
use App\Http\Controllers\Api\V1\InventarioItemController;
use App\Http\Controllers\Api\V1\InventarioMovimientoController;
use App\Http\Controllers\Api\V1\InventarioUbicacionController;
use App\Http\Controllers\Api\V1\InventarioUnidadMedidaController;
use App\Http\Controllers\Api\V1\ModuloController;
use App\Http\Controllers\Api\V1\OrdenTrabajoController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\ServicioPrecioController;
use App\Http\Controllers\Api\V1\ServicioTarifaController;
use App\Http\Controllers\Api\V1\TareaController;
use App\Http\Controllers\Api\V1\VerificacionUsuarioController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/logout', [AuthController::class, 'logout']);
    });

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::apiResource('estados-factura', EstadoFacturaController::class);
        Route::apiResource('informes', InformeController::class);
        Route::apiResource('inventario-categorias', InventarioCategoriaController::class);
        Route::apiResource('inventario-items', InventarioItemController::class);
        Route::apiResource('inventario-movimientos', InventarioMovimientoController::class);
        Route::apiResource('inventario-ubicaciones', InventarioUbicacionController::class);
        Route::apiResource('inventario-unidades-medida', InventarioUnidadMedidaController::class);
        Route::apiResource('modulos', ModuloController::class);
        Route::apiResource('roles', RoleController::class);
        Route::get('ordenes-trabajo', [OrdenTrabajoController::class, 'index']);
        Route::post('ordenes-trabajo', [OrdenTrabajoController::class, 'store']);
        Route::get('ordenes-trabajo/{orden}', [OrdenTrabajoController::class, 'show']);
        Route::put('ordenes-trabajo/{orden}', [OrdenTrabajoController::class, 'update']);
        Route::post('ordenes-trabajo/{orden}/completar', [OrdenTrabajoController::class, 'completar']);
        Route::post('ordenes-trabajo/{orden}/cancelar', [OrdenTrabajoController::class, 'cancelar']);
        Route::post('ordenes-trabajo/{orden}/generar-factura', [OrdenTrabajoController::class, 'generarFactura']);
        Route::get('facturas', [FacturaController::class, 'index']);
        Route::get('facturas/{factura}', [FacturaController::class, 'show']);
        Route::post('facturas/{factura}/marcar-pagada', [FacturaController::class, 'marcarPagada']);
        Route::post('facturas/{factura}/anular', [FacturaController::class, 'anular']);

        Route::middleware('admin')->group(function (): void {
            Route::get('admin/solicitudes-registro', [AdminSolicitudRegistroController::class, 'index']);
            Route::get('admin/solicitudes-registro/{solicitud}', [AdminSolicitudRegistroController::class, 'show']);
            Route::get('admin/documentos-verificacion/{documento}/ver', [AdminSolicitudRegistroController::class, 'verDocumento']);
            Route::post('admin/solicitudes-registro/{solicitud}/aprobar', [AdminSolicitudRegistroController::class, 'aprobar']);
            Route::post('admin/solicitudes-registro/{solicitud}/rechazar', [AdminSolicitudRegistroController::class, 'rechazar']);
        });

        Route::apiResource('clientes', ClienteController::class);
        Route::apiResource('servicios', ServicioController::class);
        Route::apiResource('servicio-precios', ServicioPrecioController::class);
        Route::apiResource('servicio-tarifas', ServicioTarifaController::class);
        Route::apiResource('tareas', TareaController::class);
        Route::apiResource('tipos-cliente', TipoClienteController::class);
        Route::apiResource('tipos-documento-identidad', TipoDocumentoIdentidadController::class);
        Route::apiResource('tipos-empresa', TipoEmpresaController::class);
        Route::apiResource('tipos-evento-facturacion', TipoEventoFacturacionController::class);
        Route::apiResource('tipos-factura', TipoFacturaController::class);
        Route::apiResource('tipos-inventario-movimiento', TipoInventarioMovimientoController::class);
        Route::apiResource('tipos-localizacion-cliente', TipoLocalizacionClienteController::class);
        Route::apiResource('tipos-rectificacion', TipoRectificacionController::class);
        Route::apiResource('tipos-registro-facturacion', TipoRegistroFacturacionController::class);
        Route::apiResource('verificaciones-usuario', VerificacionUsuarioController::class);
    });
});
