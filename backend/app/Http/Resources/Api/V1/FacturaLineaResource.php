<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacturaLineaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return $this->resource->only([
            'id',
            'orden_trabajo_linea_id',
            'servicio_id',
            'descripcion',
            'cantidad',
            'precio_unitario',
            'descuento_porcentaje',
            'base_imponible',
            'iva_porcentaje',
            'subtotal',
            'total_iva',
            'total',
            'orden'
        ]);
    }
}
