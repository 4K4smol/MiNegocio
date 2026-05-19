<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClienteRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $empresaId = $this->authenticatedEmpresaId();

        if ($empresaId !== null) {
            $this->merge(['empresa_id' => $empresaId]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = (int) ($this->authenticatedEmpresaId() ?? $this->input('empresa_id'));

        return [
            'tipo_cliente_id' => ['required', 'integer', 'exists:tipos_cliente,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'apellidos' => ['nullable', 'string', 'max:255'],
            'razon_social' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'persona_contacto' => ['nullable', 'string', 'max:255'],
            'notas' => ['nullable', 'string'],
            'activo' => ['sometimes', 'boolean'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'direccion_linea_2' => ['nullable', 'string', 'max:255'],
            'ciudad' => ['nullable', 'string', 'max:100'],
            'provincia' => ['nullable', 'string', 'max:100'],
            'codigo_postal' => ['nullable', 'string', 'max:15'],
            'pais' => ['nullable', 'string', 'max:100'],
            'empresa_id' => ['sometimes', 'nullable', 'integer', 'exists:empresas,id'],
            'dni_cif' => [
                'required',
                'string',
                'max:50',
                Rule::unique('clientes', 'dni_cif')->where(fn ($query) => $query->where('empresa_id', $empresaId)),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'dni_cif.unique' => 'Ya existe un cliente con este DNI/CIF en tu empresa.',
        ];
    }

    private function authenticatedEmpresaId(): ?int
    {
        $empresaId = $this->user()?->empresa_id;

        return $empresaId === null ? null : (int) $empresaId;
    }
}
