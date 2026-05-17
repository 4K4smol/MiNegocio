<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServicioPrecioRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $servicio = $this->route('servicio');

        $this->merge([
            'servicio_id' => $servicio !== null ? (int) $servicio : $this->input('servicio_id'),
            'moneda' => $this->input('moneda', 'EUR'),
            'iva_porcentaje' => $this->input('iva_porcentaje', 21),
            'vigente_desde' => $this->input('vigente_desde', now()->toDateTimeString()),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = (int) $this->user()?->empresa_id;

        return [
            'servicio_id' => ['required', 'integer', Rule::exists('servicios', 'id')->where(fn ($query) => $query->where('empresa_id', $empresaId))],
            'tipo_tarifa_servicio_id' => ['required', 'integer', Rule::exists('tipos_tarifa_servicio', 'id')->where(fn ($query) => $query->where('activo', true))],
            'precio_base' => ['required', 'numeric', 'min:0'],
            'iva_porcentaje' => ['required', 'numeric', 'min:0', 'max:100'],
            'retencion_porcentaje' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'moneda' => ['required', 'string', 'size:3'],
            'vigente_desde' => ['required', 'date'],
            'vigente_hasta' => ['nullable', 'date', 'after:vigente_desde'],
            'meta' => ['nullable', 'array'],
        ];
    }
}
