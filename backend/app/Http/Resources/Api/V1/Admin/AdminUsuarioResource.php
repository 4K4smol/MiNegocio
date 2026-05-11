<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminUsuarioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'nombre' => $this->nombre,
            'apellido1' => $this->apellido1,
            'apellido2' => $this->apellido2,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'activo' => (bool) $this->activo,
            'created_at' => $this->created_at,
            'role' => $this->whenLoaded('role', fn () => $this->role ? [
                'id' => $this->role->id,
                'nombre' => $this->role->nombre,
                'descripcion' => $this->role->descripcion,
            ] : null),
            'empresa' => $this->whenLoaded('empresa', fn () => $this->empresa ? [
                'id' => $this->empresa->id,
                'nombre_fiscal' => $this->empresa->nombre_fiscal,
                'nombre_comercial' => $this->empresa->nombre_comercial,
                'nif' => $this->empresa->nif,
                'activa' => (bool) $this->empresa->activa,
            ] : null),
        ];
    }
}
