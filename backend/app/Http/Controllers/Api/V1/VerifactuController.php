<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Services\VerifactuRemisionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerifactuController extends ApiController
{
    public function __construct(private readonly VerifactuRemisionService $remisionService) {}

    public function enviarPendientes(Request $request): JsonResponse
    {
        return $this->success(
            $this->remisionService->enviarPendientes($request->user()),
            'Envío simulado ejecutado.'
        );
    }
}
