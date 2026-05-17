<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTipoTarifaServicioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = (int) ($this->route('tipoTarifaServicio') ?? $this->route('id'));

        return [
            'codigo' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('tipos_tarifa_servicio', 'codigo')->ignore($id)],
            'nombre' => ['sometimes', 'required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'activo' => ['sometimes', 'boolean'],
            'orden' => ['sometimes', 'integer', 'min:0'],
            'es_sistema' => ['sometimes', 'boolean'],
        ];
    }
}
