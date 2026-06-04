<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventarioExistenciaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'empresa_id' => $this->empresa_id,
            'inventario_item_id' => $this->inventario_item_id,
            'ubicacion_id' => $this->ubicacion_id,
            'cantidad' => $this->cantidad,
            'ubicacion' => InventarioUbicacionResource::make($this->whenLoaded('ubicacion')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
