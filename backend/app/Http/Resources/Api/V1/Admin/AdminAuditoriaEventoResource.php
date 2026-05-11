<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminAuditoriaEventoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'accion' => $this->accion,
            'estado_anterior' => $this->estado_anterior,
            'estado_nuevo' => $this->estado_nuevo,
            'motivo' => $this->motivo,
            'metadatos' => $this->metadatos,
            'created_at' => $this->created_at,
            'admin' => $this->whenLoaded('admin', fn () => $this->admin ? [
                'id' => $this->admin->id,
                'nombre' => $this->admin->nombre ?? $this->admin->name,
                'email' => $this->admin->email,
            ] : null),
            'usuario' => $this->whenLoaded('usuario', fn () => $this->usuario ? [
                'id' => $this->usuario->id,
                'nombre' => $this->usuario->nombre ?? $this->usuario->name,
                'email' => $this->usuario->email,
            ] : null),
            'empresa' => $this->whenLoaded('empresa', fn () => $this->empresa ? [
                'id' => $this->empresa->id,
                'nombre_fiscal' => $this->empresa->nombre_fiscal,
                'nif' => $this->empresa->nif,
            ] : null),
            'solicitud_verificacion_id' => $this->solicitud_verificacion_id,
        ];
    }
}
