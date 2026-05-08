<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SolicitudRegistroResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'empresa_id' => $this->empresa_id,
            'nombre_fiscal' => $this->empresa?->nombre_fiscal,
            'nif' => $this->empresa?->nif,
            'estado_verificacion' => $this->estadoVerificacion?->nombre,
            'observaciones' => $this->observaciones,
            'fecha_revision' => $this->fecha_revision,
            'documentos' => DocumentoVerificacionResource::collection($this->whenLoaded('documentos')),
        ];
    }
}
