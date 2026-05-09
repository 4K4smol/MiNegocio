<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;

class VerifactuController extends ApiController
{
    public function enviarPendientes(): JsonResponse
    {
        return $this->success(
            ['enviados' => 0],
            'Stub: envío de pendientes aún no implementado.'
        );
    }
}
