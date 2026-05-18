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
        $costeUnitario = $this->coste_unitario !== null ? (float) $this->coste_unitario : 0.0;

        return [
            'id' => $this->id,
            'empresa_id' => $this->empresa_id,
            'unidad_medida_id' => $this->unidad_medida_id,
            'ubicacion_id' => $this->ubicacion_id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'sku' => $this->sku,
            'codigo_barras' => $this->codigo_barras,
            'cantidad' => $this->stock_actual,
            'stock_actual' => $this->stock_actual,
            'stock_minimo' => $this->stock_minimo,
            'coste_unitario' => $this->coste_unitario,
            'stock_bajo' => $stockActual <= $stockMinimo,
            'valor_stock' => round($stockActual * $costeUnitario, 2),
            'activo' => $this->activo,
            'unidad_medida' => InventarioUnidadMedidaResource::make($this->whenLoaded('unidadMedida')),
            'ubicacion' => InventarioUbicacionResource::make($this->whenLoaded('ubicacion')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
