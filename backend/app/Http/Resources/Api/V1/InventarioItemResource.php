<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventarioItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'empresa_id' => $this->empresa_id,
            'categoria_id' => $this->categoria_id,
            'unidad_medida_id' => $this->unidad_medida_id,
            'ubicacion_id' => $this->ubicacion_id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'sku' => $this->sku,
            'codigo_barras' => $this->codigo_barras,
            'stock_actual' => $this->stock_actual,
            'stock_minimo' => $this->stock_minimo,
            'coste_unitario' => $this->coste_unitario,
            'activo' => $this->activo,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
