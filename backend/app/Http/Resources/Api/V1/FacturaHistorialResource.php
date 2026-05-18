<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacturaHistorialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'factura_id' => $this->factura_id,
            'empresa_id' => $this->empresa_id,
            'user_id' => $this->user_id,
            'accion' => $this->accion,
            'estado_anterior_id' => $this->estado_anterior_id,
            'estado_nuevo_id' => $this->estado_nuevo_id,
            'descripcion' => $this->descripcion,
            'metadatos' => $this->metadatos,
            'created_at' => $this->created_at,
        ];
    }
}
