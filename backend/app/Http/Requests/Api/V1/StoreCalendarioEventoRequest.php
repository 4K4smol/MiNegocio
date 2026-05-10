<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\Cliente;
use App\Models\OrdenTrabajo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreCalendarioEventoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:140'],
            'inicio' => ['required', 'date'],
            'fin' => ['nullable', 'date', 'after_or_equal:inicio'],
            'tipo' => ['required', 'string', 'max:30'],
            'cliente_id' => ['nullable', 'integer', 'exists:clientes,id'],
            'orden_trabajo_id' => ['nullable', 'integer', 'exists:ordenes_trabajo,id'],
            'descripcion' => ['nullable', 'string'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'todo_el_dia' => ['sometimes', 'boolean'],
            'estado_codigo' => ['nullable', 'string', 'max:30'],
            'meta' => ['nullable', 'array'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $user = $this->user();
            if ($user === null || strtolower((string) $user->role?->nombre) === 'admin') {
                return;
            }

            if ($this->filled('cliente_id')) {
                $clienteOk = Cliente::query()
                    ->whereKey((int) $this->input('cliente_id'))
                    ->where('empresa_id', $user->empresa_id)
                    ->exists();

                if (! $clienteOk) {
                    $validator->errors()->add('cliente_id', 'El cliente no pertenece a la empresa autenticada.');
                }
            }

            if ($this->filled('orden_trabajo_id')) {
                $ordenOk = OrdenTrabajo::query()
                    ->whereKey((int) $this->input('orden_trabajo_id'))
                    ->where('empresa_id', $user->empresa_id)
                    ->exists();

                if (! $ordenOk) {
                    $validator->errors()->add('orden_trabajo_id', 'La orden de trabajo no pertenece a la empresa autenticada.');
                }
            }
        });
    }
}
