<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServicioPrecioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'servicio_id' => $this->servicio_id,
            'tipo_tarifa_servicio_id' => $this->tipo_tarifa_servicio_id,
            'precio_base' => $this->precio_base,
            'iva_porcentaje' => $this->iva_porcentaje,
            'retencion_porcentaje' => $this->retencion_porcentaje,
            'moneda' => $this->moneda,
            'meta' => $this->meta,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'tarifa' => TipoTarifaServicioResource::make($this->whenLoaded('tarifa')),
            'tipo_tarifa_servicio' => TipoTarifaServicioResource::make($this->whenLoaded('tipoTarifaServicio')),
            'servicio' => ServicioResource::make($this->whenLoaded('servicio')),
        ];
    }
}
