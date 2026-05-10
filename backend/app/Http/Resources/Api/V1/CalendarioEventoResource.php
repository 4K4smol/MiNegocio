<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CalendarioEventoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'empresa_id' => $this->empresa_id,
            'cliente' => ClienteResource::make($this->whenLoaded('cliente')),
            'orden_trabajo' => OrdenTrabajoResource::make($this->whenLoaded('ordenTrabajo')),
            'titulo' => $this->titulo,
            'descripcion' => $this->descripcion,
            'tipo' => $this->tipo,
            'inicio' => $this->inicio,
            'fin' => $this->fin,
            'todo_el_dia' => (bool) $this->todo_el_dia,
            'estado_codigo' => $this->estado_codigo,
            'ubicacion' => $this->ubicacion,
            'meta' => $this->meta,
            'creador' => $this->whenLoaded('creador', fn () => $this->creador?->only(['id', 'name', 'email'])),
        ];
    }
}
