<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo_cliente_id' => ['sometimes', 'integer', 'exists:tipos_cliente,id'],
            'nombre' => ['sometimes', 'string', 'max:255'],
            'apellidos' => ['nullable', 'string', 'max:255'],
            'razon_social' => ['nullable', 'string', 'max:255'],
            'dni_cif' => ['sometimes', 'string', 'max:50'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'persona_contacto' => ['nullable', 'string', 'max:255'],
            'notas' => ['nullable', 'string'],
            'activo' => ['sometimes', 'boolean'],
            'empresa_id' => ['sometimes', 'integer', 'exists:empresas,id'],
        ];
    }
}
