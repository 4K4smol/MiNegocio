<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrdenTrabajoLineaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'servicio_id' => $this->servicio_id,
            'servicio_nombre' => $this->whenLoaded('servicio', fn () => $this->servicio?->nombre),
            'descripcion' => $this->descripcion,
            'unidad_snapshot' => $this->unidad_snapshot,
            'cantidad' => (float) $this->cantidad,
            'precio_unitario' => (float) $this->precio_unitario,
            'descuento_porcentaje' => (float) $this->descuento_porcentaje,
            'base_imponible' => (float) $this->base_imponible,
            'iva_porcentaje' => (float) $this->iva_porcentaje,
            'cuota_iva' => (float) $this->cuota_iva,
            'total' => (float) $this->total,
            'facturable' => (bool) $this->facturable,
            'facturado_cantidad' => (float) $this->facturado_cantidad,
            'meta' => $this->meta,
        ];
    }
}
