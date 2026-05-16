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
            'servicio_tarifa_id' => $this->servicio_tarifa_id,
            'precio_base' => $this->precio_base,
            'iva_porcentaje' => $this->iva_porcentaje,
            'retencion_porcentaje' => $this->retencion_porcentaje,
            'moneda' => $this->moneda,
            'vigente_desde' => $this->vigente_desde,
            'vigente_hasta' => $this->vigente_hasta,
            'meta' => $this->meta,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'tarifa' => ServicioTarifaResource::make($this->whenLoaded('tarifa')),
            'servicio' => ServicioResource::make($this->whenLoaded('servicio')),
        ];
    }
}
