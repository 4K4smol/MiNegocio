<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreServicioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'empresa_id' => ['required', 'integer', 'exists:empresas,id'],
            'tipo_negocio' => ['required', 'string', 'max:50'],
            'codigo' => ['required', 'string', 'max:50'],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'unidad_servicio' => ['required', 'string', 'max:50'],
            'duracion_estimada_min' => ['nullable', 'integer', 'min:0'],
            'activo' => ['sometimes', 'boolean'],
            'meta' => ['nullable', 'array'],
        ];
    }
}
