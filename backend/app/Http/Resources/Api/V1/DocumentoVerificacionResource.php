<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentoVerificacionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tipo_documento' => $this->tipo_documento,
            'url_preview' => url("/api/v1/admin/documentos-verificacion/{$this->id}/preview"),
            'nombre_original' => $this->nombre_original,
            'mime_type' => $this->mime_type,
            'tamano' => $this->tamano,
            'hash_sha256' => $this->hash_sha256,
            'created_at' => $this->created_at,
        ];
    }
}
