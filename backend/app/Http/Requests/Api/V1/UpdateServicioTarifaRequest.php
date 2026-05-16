<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServicioTarifaRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->user()?->empresa_id !== null) {
            $this->merge(['empresa_id' => $this->user()->empresa_id]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = (int) $this->input('empresa_id');
        $id = (int) ($this->route('servicio_tarifa') ?? $this->route('id'));

        return [
            'empresa_id' => ['sometimes', 'nullable', 'integer', 'exists:empresas,id'],
            'codigo' => ['sometimes', 'required', 'string', 'max:30', Rule::unique('servicio_tarifas', 'codigo')->where(fn ($query) => $query->where('empresa_id', $empresaId))->ignore($id)],
            'nombre' => ['sometimes', 'required', 'string', 'max:100', Rule::unique('servicio_tarifas', 'nombre')->where(fn ($query) => $query->where('empresa_id', $empresaId))->ignore($id)],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'es_default' => ['sometimes', 'boolean'],
            'activo' => ['sometimes', 'boolean'],
        ];
    }
}
