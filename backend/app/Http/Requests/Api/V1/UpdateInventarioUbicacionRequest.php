<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateInventarioUbicacionRequest extends FormRequest
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
        $empresaId = (int) $this->input('empresa_id', $this->user()?->empresa_id);
        $id = (int) $this->route('id');

        return [
            'empresa_id' => ['sometimes','integer','exists:empresas,id'],
            'nombre' => ['sometimes','string','max:255', Rule::unique('inventario_ubicaciones', 'nombre')->where(fn ($query) => $query->where('empresa_id', $empresaId))->ignore($id)],
            'descripcion' => ['nullable','string'],
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
                    $validator->errors()->add('empresa_id', 'No puedes editar ubicaciones de otra empresa.');
                }
            },
        ];
    }

    private function esAdmin(): bool
    {
        return $this->user()?->role?->nombre === 'admin';
    }
}
