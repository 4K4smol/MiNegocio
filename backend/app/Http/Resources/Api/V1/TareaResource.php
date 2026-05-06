<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TareaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'orden_trabajo_id' => $this->orden_trabajo_id,
            'orden_trabajo_linea_id' => $this->orden_trabajo_linea_id,
            'responsable_id' => $this->responsable_id,
            'titulo' => $this->titulo,
            'descripcion' => $this->descripcion,
            'estado_codigo' => $this->estado_codigo,
            'fecha_vencimiento' => $this->fecha_vencimiento,
            'fecha_completada' => $this->fecha_completada,
            'orden' => $this->orden,
            'activa' => $this->activa,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
