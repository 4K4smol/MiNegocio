<?php

declare(strict_types=1);

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
use App\Http\Controllers\Api\V1\InformeController;
use App\Http\Controllers\Api\V1\InventarioCategoriaController;
use App\Http\Controllers\Api\V1\InventarioItemController;
use App\Http\Controllers\Api\V1\InventarioMovimientoController;
use App\Http\Controllers\Api\V1\InventarioUbicacionController;
use App\Http\Controllers\Api\V1\InventarioUnidadMedidaController;
use App\Http\Controllers\Api\V1\ModuloController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\ServicioPrecioController;
use App\Http\Controllers\Api\V1\ServicioTarifaController;
use App\Http\Controllers\Api\V1\TareaController;
use App\Http\Controllers\Api\V1\VerificacionUsuarioController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/logout', [AuthController::class, 'logout']);
    });

    Route::apiResource('estados-factura', EstadoFacturaController::class);
    Route::apiResource('informes', InformeController::class);
    Route::apiResource('inventario-categorias', InventarioCategoriaController::class);
    Route::apiResource('inventario-items', InventarioItemController::class);
    Route::apiResource('inventario-movimientos', InventarioMovimientoController::class);
    Route::apiResource('inventario-ubicaciones', InventarioUbicacionController::class);
    Route::apiResource('inventario-unidades-medida', InventarioUnidadMedidaController::class);
    Route::apiResource('modulos', ModuloController::class);
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('servicio-precios', ServicioPrecioController::class);
    Route::apiResource('servicio-tarifas', ServicioTarifaController::class);
    Route::apiResource('tareas', TareaController::class);
    Route::apiResource('verificaciones-usuario', VerificacionUsuarioController::class);

    Route::apiResource('clientes', ClienteController::class);
    Route::apiResource('servicios', ServicioController::class);
    Route::apiResource('tipos-cliente', TipoClienteController::class);
    Route::apiResource('tipos-documento-identidad', TipoDocumentoIdentidadController::class);
    Route::apiResource('tipos-empresa', TipoEmpresaController::class);
    Route::apiResource('tipos-evento-facturacion', TipoEventoFacturacionController::class);
    Route::apiResource('tipos-factura', TipoFacturaController::class);
    Route::apiResource('tipos-inventario-movimiento', TipoInventarioMovimientoController::class);
    Route::apiResource('tipos-localizacion-cliente', TipoLocalizacionClienteController::class);
    Route::apiResource('tipos-rectificacion', TipoRectificacionController::class);
    Route::apiResource('tipos-registro-facturacion', TipoRegistroFacturacionController::class);
});
