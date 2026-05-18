<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacturaCobroResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'factura_id' => $this->factura_id,
            'empresa_id' => $this->empresa_id,
            'fecha_cobro' => $this->fecha_cobro,
            'importe' => (float) $this->importe,
            'metodo_pago' => $this->metodo_pago,
            'referencia' => $this->referencia,
            'observaciones' => $this->observaciones,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
        ];
    }
}
