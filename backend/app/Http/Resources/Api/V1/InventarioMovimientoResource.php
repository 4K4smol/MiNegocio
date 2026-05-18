<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventarioMovimientoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'empresa_id' => $this->empresa_id,
            'inventario_item_id' => $this->inventario_item_id,
            'ubicacion_origen_id' => $this->ubicacion_origen_id,
            'ubicacion_destino_id' => $this->ubicacion_destino_id,
            'tipo_movimiento_id' => $this->tipo_movimiento_id,
            'cantidad' => $this->cantidad,
            'stock_anterior' => $this->stock_anterior,
            'stock_posterior' => $this->stock_posterior,
            'motivo' => $this->motivo,
            'fecha_movimiento' => $this->fecha_movimiento,
            'user_id' => $this->user_id,
            'item' => InventarioItemResource::make($this->whenLoaded('item')),
            'tipo_movimiento' => TipoInventarioMovimientoResource::make($this->whenLoaded('tipoMovimiento')),
            'ubicacion_origen' => InventarioUbicacionResource::make($this->whenLoaded('ubicacionOrigen')),
            'ubicacion_destino' => InventarioUbicacionResource::make($this->whenLoaded('ubicacionDestino')),
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user?->id,
                'nombre' => $this->user?->nombre,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
