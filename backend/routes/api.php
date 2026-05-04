<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\ClienteController;
use App\Http\Controllers\Api\V1\ServicioController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::apiResource('clientes', ClienteController::class);
    Route::apiResource('servicios', ServicioController::class);
});
