<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminDocumentoVerificacionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tipo_documento' => $this->tipo_documento,
            'nombre_original' => $this->nombre_original,
            'mime_type' => $this->mime_type,
            'tamano' => $this->tamano,
            'preview_url' => url("/api/v1/admin/documentos-verificacion/{$this->id}/preview"),
            'created_at' => $this->created_at,
        ];
    }
}
