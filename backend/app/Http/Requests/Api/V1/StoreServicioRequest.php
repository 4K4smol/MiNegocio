<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServicioRequest extends FormRequest
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

        return [
            'empresa_id' => ['sometimes', 'nullable', 'integer', 'exists:empresas,id'],
            'tipo_negocio' => ['nullable', 'string', 'max:50'],
            'codigo' => ['nullable', 'string', 'max:50', Rule::unique('servicios', 'codigo')->where(fn ($query) => $query->where('empresa_id', $empresaId))],
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'unidad_servicio' => ['required', 'string', 'max:30'],
            'duracion_estimada_min' => ['nullable', 'integer', 'min:0'],
            'activo' => ['sometimes', 'boolean'],
            'meta' => ['nullable', 'array'],
            'precio_base' => ['nullable', 'numeric', 'min:0'],
            'iva_porcentaje' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'retencion_porcentaje' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'moneda' => ['nullable', 'string', 'size:3'],
            'vigente_desde' => ['nullable', 'date'],
        ];
    }
}
