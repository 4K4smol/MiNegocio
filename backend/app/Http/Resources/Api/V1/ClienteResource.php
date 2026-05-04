<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClienteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tipo_cliente_id' => $this->tipo_cliente_id,
            'nombre' => $this->nombre,
            'apellidos' => $this->apellidos,
            'razon_social' => $this->razon_social,
            'dni_cif' => $this->dni_cif,
            'telefono' => $this->telefono,
            'email' => $this->email,
            'persona_contacto' => $this->persona_contacto,
            'notas' => $this->notas,
            'activo' => $this->activo,
            'empresa_id' => $this->empresa_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'tipo_cliente' => $this->whenLoaded('tipoCliente'),
            'empresa' => $this->whenLoaded('empresa'),
        ];
    }
}
