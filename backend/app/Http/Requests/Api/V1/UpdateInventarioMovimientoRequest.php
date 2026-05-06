<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventarioMovimientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'empresa_id' => ['sometimes','integer','exists:empresas,id'],
            'inventario_item_id' => ['sometimes','integer','exists:inventario_items,id'],
            'ubicacion_origen_id' => ['nullable','integer','exists:inventario_ubicaciones,id'],
            'ubicacion_destino_id' => ['nullable','integer','exists:inventario_ubicaciones,id'],
            'tipo_movimiento_id' => ['sometimes','integer','exists:tipos_inventario_movimiento,id'],
            'cantidad' => ['sometimes','numeric'],
            'stock_anterior' => ['nullable','numeric'],
            'stock_posterior' => ['nullable','numeric'],
            'motivo' => ['nullable','string'],
            'fecha_movimiento' => ['nullable','date'],
            'user_id' => ['nullable','integer','exists:users,id'],
        ];
    }
}
