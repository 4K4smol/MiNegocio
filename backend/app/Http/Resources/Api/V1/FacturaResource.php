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
            'tipo_factura' => $this->tipoFactura?->codigo,
            'estado_factura' => $this->estadoFactura?->codigo,
            'serie' => $this->serie,
            'numero' => $this->numero,
            'fecha_emision' => $this->fecha_emision,
            'fecha_operacion' => $this->fecha_operacion,
            'emisor_nif' => $this->emisor_nif,
            'emisor_nombre_razon_social' => $this->emisor_nombre_razon_social,
            'receptor_nif' => $this->receptor_nif,
            'receptor_nombre_razon_social' => $this->receptor_nombre_razon_social,
            'subtotal' => (float) $this->subtotal,
            'cuota_iva' => (float) $this->cuota_iva,
            'total' => (float) $this->total,
            'pagada' => (bool) $this->pagada,
            'fecha_pago' => $this->fecha_pago,
            'lineas' => FacturaLineaResource::collection($this->whenLoaded('lineas')),
            'impuestos' => FacturaImpuestoResource::collection($this->whenLoaded('impuestos')),
        ];
    }
}
