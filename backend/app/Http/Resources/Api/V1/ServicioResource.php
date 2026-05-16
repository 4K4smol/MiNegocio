<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServicioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'empresa_id' => $this->empresa_id,
            'tipo_negocio' => $this->tipo_negocio,
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'unidad_servicio' => $this->unidad_servicio,
            'duracion_estimada_min' => $this->duracion_estimada_min,
            'activo' => $this->activo,
            'meta' => $this->meta,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'empresa' => $this->whenLoaded('empresa'),
            'precios' => ServicioPrecioResource::collection($this->whenLoaded('precios')),
            'precios_count' => $this->whenCounted('precios'),
        ];
    }
}
