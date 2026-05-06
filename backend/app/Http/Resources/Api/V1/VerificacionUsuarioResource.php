<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VerificacionUsuarioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'estado_verificacion_id' => $this->estado_verificacion_id,
            'tipo_documento_identidad_id' => $this->tipo_documento_identidad_id,
            'numero_documento' => $this->numero_documento,
            'ruta_documento_anverso' => $this->ruta_documento_anverso,
            'ruta_documento_reverso' => $this->ruta_documento_reverso,
            'ruta_selfie' => $this->ruta_selfie,
            'fecha_verificacion' => $this->fecha_verificacion,
            'notas_revision' => $this->notas_revision,
            'revisado_por' => $this->revisado_por,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
