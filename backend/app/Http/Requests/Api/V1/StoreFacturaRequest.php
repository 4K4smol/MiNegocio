<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreFacturaRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->user()?->empresa_id !== null) {
            $this->merge(['empresa_id' => (int) $this->user()->empresa_id]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'empresa_id' => ['sometimes', 'nullable', 'integer', 'exists:empresas,id'],
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'tipo_factura_codigo' => ['sometimes', 'string', 'exists:tipos_factura,codigo'],
            'serie' => ['sometimes', 'nullable', 'string', 'max:20'],
            'fecha_emision' => ['sometimes', 'date'],
            'fecha_operacion' => ['sometimes', 'nullable', 'date'],
            'fecha_vencimiento' => ['sometimes', 'nullable', 'date', 'after_or_equal:fecha_emision'],
            'moneda' => ['sometimes', 'string', 'size:3'],
            'observaciones' => ['sometimes', 'nullable', 'string'],
            'metadatos' => ['sometimes', 'nullable', 'array'],
            'lineas' => ['sometimes', 'array'],
            'lineas.*.servicio_id' => ['sometimes', 'nullable', 'integer', 'exists:servicios,id'],
            'lineas.*.orden_trabajo_linea_id' => ['sometimes', 'nullable', 'integer', 'exists:ordenes_trabajo_lineas,id'],
            'lineas.*.concepto' => ['sometimes', 'nullable', 'string', 'max:255'],
            'lineas.*.descripcion' => ['sometimes', 'nullable', 'string'],
            'lineas.*.cantidad' => ['required_with:lineas', 'numeric', 'gt:0'],
            'lineas.*.precio_unitario' => ['required_with:lineas', 'numeric', 'min:0'],
            'lineas.*.iva_porcentaje' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'lineas.*.retencion_porcentaje' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'lineas.*.descuento_porcentaje' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'lineas.*.orden' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
