<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacturaImpuestoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return $this->resource->only([
            'id',
            'tipo_impuesto',
            'impuesto_codigo',
            'impuesto_nombre',
            'base',
            'base_imponible',
            'porcentaje',
            'tipo_porcentaje',
            'cuota'
        ]);
    }
}
