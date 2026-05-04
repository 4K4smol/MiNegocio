<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServicioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'empresa_id' => ['sometimes', 'integer', 'exists:empresas,id'],
            'tipo_negocio' => ['sometimes', 'string', 'max:50'],
            'codigo' => ['sometimes', 'string', 'max:50'],
            'nombre' => ['sometimes', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'unidad_servicio' => ['sometimes', 'string', 'max:50'],
            'duracion_estimada_min' => ['nullable', 'integer', 'min:0'],
            'activo' => ['sometimes', 'boolean'],
            'meta' => ['nullable', 'array'],
        ];
    }
}
