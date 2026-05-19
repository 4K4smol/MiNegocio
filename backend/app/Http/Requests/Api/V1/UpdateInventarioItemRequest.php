<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateInventarioItemRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->filled('cantidad') && !$this->filled('stock_actual')) {
            $this->merge(['stock_actual' => $this->input('cantidad')]);
        }

        if (!$this->esAdmin() && $this->user()?->empresa_id !== null) {
            $this->merge(['empresa_id' => $this->user()->empresa_id]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = (int) $this->input('empresa_id', $this->user()?->empresa_id);
        $id = (int) $this->route('id');

        return [
            'empresa_id' => ['sometimes','integer','exists:empresas,id'],
            'unidad_medida_id' => ['sometimes','required','integer','exists:inventario_unidades_medida,id'],
            'ubicacion_id' => ['nullable','integer', Rule::exists('inventario_ubicaciones', 'id')->where(fn ($query) => $query->where('empresa_id', $empresaId))],
            'nombre' => ['sometimes','string','max:255'],
            'descripcion' => ['nullable','string'],
            'stock_actual' => ['nullable','numeric','min:0'],
            'stock_minimo' => ['nullable','numeric','min:0'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (!$this->esAdmin() && (int) $this->input('empresa_id') !== (int) $this->user()?->empresa_id) {
                    $validator->errors()->add('empresa_id', 'No puedes editar items en otra empresa.');
                }
            },
        ];
    }

    private function esAdmin(): bool
    {
        return $this->user()?->role?->nombre === 'admin';
    }
}
