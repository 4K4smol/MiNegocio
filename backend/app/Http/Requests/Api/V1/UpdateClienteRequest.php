<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClienteRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $user = $this->user();

        if ($user?->role?->nombre !== 'admin') {
            $this->merge([
                'empresa_id' => $user?->empresa_id,
            ]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = (int) $this->input('empresa_id');
        $routeCliente = $this->route('cliente') ?? $this->route('id');
        $id = is_object($routeCliente) && method_exists($routeCliente, 'getKey')
            ? (int) $routeCliente->getKey()
            : (int) $routeCliente;

        return [
            'tipo_cliente_id' => ['sometimes', 'integer', 'exists:tipos_cliente,id'],
            'nombre' => ['sometimes', 'string', 'max:255'],
            'apellidos' => ['nullable', 'string', 'max:255'],
            'razon_social' => ['nullable', 'string', 'max:255'],
            'dni_cif' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('clientes', 'dni_cif')->where(fn ($query) => $query->where('empresa_id', $empresaId))->ignore($id),
            ],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'persona_contacto' => ['nullable', 'string', 'max:255'],
            'notas' => ['nullable', 'string'],
            'activo' => ['sometimes', 'boolean'],
            'empresa_id' => ['sometimes', 'integer', 'exists:empresas,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'dni_cif.unique' => 'Ya existe un cliente con este DNI/CIF en tu empresa.',
        ];
    }
}
