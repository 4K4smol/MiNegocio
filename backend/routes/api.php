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
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/logout', [AuthController::class, 'logout']);
    });

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
