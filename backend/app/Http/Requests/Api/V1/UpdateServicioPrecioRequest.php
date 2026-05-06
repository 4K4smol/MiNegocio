<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServicioPrecioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'servicio_id' => ['sometimes','integer','exists:servicios,id'],
            'servicio_tarifa_id' => ['sometimes','integer','exists:servicio_tarifas,id'],
            'precio_base' => ['sometimes','numeric','min:0'],
            'iva_porcentaje' => ['nullable','numeric','min:0'],
            'retencion_porcentaje' => ['nullable','numeric','min:0'],
            'moneda' => ['sometimes','string','size:3'],
            'vigente_desde' => ['nullable','date'],
            'vigente_hasta' => ['nullable','date'],
            'meta' => ['nullable','array'],
        ];
    }
}
