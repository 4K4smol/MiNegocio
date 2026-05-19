<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventarioItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $stockActual = (float) $this->stock_actual;
        $stockMinimo = (float) $this->stock_minimo;

        return [
            'id' => $this->id,
            'empresa_id' => $this->empresa_id,
            'unidad_medida_id' => $this->unidad_medida_id,
            'ubicacion_id' => $this->ubicacion_id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'cantidad' => $this->stock_actual,
            'stock_actual' => $this->stock_actual,
            'stock_minimo' => $this->stock_minimo,
            'stock_bajo' => $stockActual <= $stockMinimo,
            'unidad_medida' => InventarioUnidadMedidaResource::make($this->whenLoaded('unidadMedida')),
            'ubicacion' => InventarioUbicacionResource::make($this->whenLoaded('ubicacion')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
