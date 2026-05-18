<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInventarioUnidadMedidaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = (int) $this->route('id');

        return [
            'codigo' => ['sometimes','string','max:20', Rule::unique('inventario_unidades_medida', 'codigo')->ignore($id)],
            'nombre' => ['sometimes','string','max:255'],
        ];
    }
}
