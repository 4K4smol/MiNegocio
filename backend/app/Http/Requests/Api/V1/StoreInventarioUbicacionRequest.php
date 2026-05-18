<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreInventarioUbicacionRequest extends FormRequest
{
    private ?int $empresaIdSolicitada = null;

    protected function prepareForValidation(): void
    {
        $this->empresaIdSolicitada = $this->filled('empresa_id')
            ? (int) $this->input('empresa_id')
            : null;

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
            'nombre' => ['required','string','max:255', Rule::unique('inventario_ubicaciones', 'nombre')->where(fn ($query) => $query->where('empresa_id', $empresaId))],
            'descripcion' => ['nullable','string'],
            'direccion' => ['nullable','string','max:255'],
            'observaciones' => ['nullable','string'],
            'activo' => ['sometimes','boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (
                    ! $this->esAdmin()
                    && $this->empresaIdSolicitada !== null
                    && $this->empresaIdSolicitada !== (int) $this->user()?->empresa_id
                ) {
                    $validator->errors()->add('empresa_id', 'No puedes crear ubicaciones en otra empresa.');
                }
            },
        ];
    }

    private function esAdmin(): bool
    {
        return $this->user()?->role?->nombre === 'admin';
    }
}
