<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InformeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'empresa_id' => $this->empresa_id,
            'generado_por' => $this->generado_por,
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'tipo' => $this->tipo,
            'formato' => $this->formato,
            'estado_codigo' => $this->estado_codigo,
            'filtros' => $this->filtros,
            'resumen' => $this->resumen,
            'contenido' => $this->contenido,
            'generado_en' => $this->generado_en,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
