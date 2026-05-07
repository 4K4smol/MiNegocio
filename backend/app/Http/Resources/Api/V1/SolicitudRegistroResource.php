<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SolicitudRegistroResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['empresa_id'=>$this->id,'nombre_fiscal'=>$this->nombre_fiscal,'nif'=>$this->nif,'activa'=>$this->activa,'estado_verificacion'=>$this->verificacion?->estadoVerificacion?->nombre];
    }
}
