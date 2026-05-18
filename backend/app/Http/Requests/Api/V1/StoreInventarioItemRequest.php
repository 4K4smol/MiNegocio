<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreInventarioItemRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->esAdmin() && $this->user()?->empresa_id !== null) {
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
            'empresa_id' => [$this->esAdmin() ? 'required' : 'sometimes','integer','exists:empresas,id'],
            'unidad_medida_id' => ['required','integer','exists:inventario_unidades_medida,id'],
            'ubicacion_id' => ['nullable','integer', Rule::exists('inventario_ubicaciones', 'id')->where(fn ($query) => $query->where('empresa_id', $empresaId))],
            'nombre' => ['required','string','max:255'],
            'descripcion' => ['nullable','string'],
            'sku' => ['nullable','string','max:100', Rule::unique('inventario_items', 'sku')->where(fn ($query) => $query->where('empresa_id', $empresaId))],
            'codigo_barras' => ['nullable','string','max:100'],
            'stock_actual' => ['nullable','numeric','min:0'],
            'stock_minimo' => ['nullable','numeric','min:0'],
            'coste_unitario' => ['nullable','numeric','min:0'],
            'activo' => ['sometimes','boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (! $this->esAdmin() && (int) $this->input('empresa_id') !== (int) $this->user()?->empresa_id) {
                    $validator->errors()->add('empresa_id', 'No puedes crear items en otra empresa.');
                }
            },
        ];
    }

    private function esAdmin(): bool
    {
        return $this->user()?->role?->nombre === 'admin';
    }
}
