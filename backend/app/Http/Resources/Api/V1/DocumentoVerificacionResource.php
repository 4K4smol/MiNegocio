<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentoVerificacionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id'=>$this->id,'tipo_documento'=>$this->tipo_documento,'nombre_original'=>$this->nombre_original,'mime_type'=>$this->mime_type,'tamano'=>$this->tamano,'estado'=>$this->estadoVerificacion?->nombre];
    }
}
