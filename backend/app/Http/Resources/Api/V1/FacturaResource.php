<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacturaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'empresa_id' => $this->empresa_id,
            'cliente_id' => $this->cliente_id,
            'serie' => $this->serie,
            'numero' => $this->numero,
            'fecha_emision' => $this->fecha_emision,
            'subtotal' => (float) $this->subtotal,
            'cuota_iva' => (float) $this->cuota_iva,
            'total' => (float) $this->total,
            'lineas' => FacturaLineaResource::collection($this->whenLoaded('lineas')),
            'impuestos' => FacturaImpuestoResource::collection($this->whenLoaded('impuestos')),
        ];
    }
}
