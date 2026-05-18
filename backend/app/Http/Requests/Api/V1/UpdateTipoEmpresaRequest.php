<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTipoEmpresaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = (int) $this->route('id');

        return [
            'nombre' => ['sometimes', 'string', 'max:50', Rule::unique('tipos_empresa', 'nombre')->ignore($id)],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ];
    }
}
