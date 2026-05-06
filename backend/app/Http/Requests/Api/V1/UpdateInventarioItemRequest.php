<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventarioItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'empresa_id' => ['sometimes','integer','exists:empresas,id'],
            'categoria_id' => ['nullable','integer','exists:inventario_categorias,id'],
            'unidad_medida_id' => ['nullable','integer','exists:inventario_unidades_medida,id'],
            'ubicacion_id' => ['nullable','integer','exists:inventario_ubicaciones,id'],
            'nombre' => ['sometimes','string','max:255'],
            'descripcion' => ['nullable','string'],
            'sku' => ['nullable','string','max:100'],
            'codigo_barras' => ['nullable','string','max:100'],
            'stock_actual' => ['nullable','numeric'],
            'stock_minimo' => ['nullable','numeric'],
            'coste_unitario' => ['nullable','numeric','min:0'],
            'activo' => ['sometimes','boolean'],
        ];
    }
}
