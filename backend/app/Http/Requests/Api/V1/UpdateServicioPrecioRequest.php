<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServicioPrecioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = (int) $this->user()?->empresa_id;

        return [
            'servicio_id' => ['sometimes', 'required', 'integer', Rule::exists('servicios', 'id')->where(fn ($query) => $query->where('empresa_id', $empresaId))],
            'tipo_tarifa_servicio_id' => ['sometimes', 'required', 'integer', Rule::exists('tipos_tarifa_servicio', 'id')->where(fn ($query) => $query->where('activo', true))],
            'precio_base' => ['sometimes', 'required', 'numeric', 'min:0'],
            'iva_porcentaje' => ['sometimes', 'required', 'numeric', 'min:0', 'max:100'],
            'retencion_porcentaje' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'moneda' => ['sometimes', 'required', 'string', 'size:3'],
            'vigente_desde' => ['sometimes', 'required', 'date'],
            'vigente_hasta' => ['nullable', 'date', 'after:vigente_desde'],
            'meta' => ['nullable', 'array'],
        ];
    }
}
