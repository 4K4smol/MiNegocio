<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\Cliente;
use App\Models\Servicio;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreOrdenTrabajoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'prioridad_id' => ['nullable', 'integer', 'exists:orden_trabajo_prioridades,id'],
            'fecha_programada_inicio' => ['nullable', 'date'],
            'fecha_programada_fin' => ['nullable', 'date', 'after_or_equal:fecha_programada_inicio'],
            'tecnico_responsable_id' => ['nullable', 'integer', 'exists:users,id'],
            'notas_cliente' => ['nullable', 'string'],
            'notas_internas' => ['nullable', 'string'],
            'lineas' => ['required', 'array', 'min:1'],
            'lineas.*.servicio_id' => ['required', 'integer', 'exists:servicios,id'],
            'lineas.*.descripcion' => ['nullable', 'string'],
            'lineas.*.cantidad' => ['required', 'numeric', 'min:0.01'],
            'lineas.*.precio_unitario' => ['required', 'numeric', 'min:0'],
            'lineas.*.descuento_porcentaje' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lineas.*.iva_porcentaje' => ['required', 'numeric', 'min:0', 'max:100'],
            'lineas.*.facturable' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $user = $this->user();
            if ($user === null || $user->role?->nombre === 'admin') { return; }

            $clienteId = (int) $this->input('cliente_id');
            $clienteOk = Cliente::query()->whereKey($clienteId)->where('empresa_id', $user->empresa_id)->exists();
            if (! $clienteOk) {
                $validator->errors()->add('cliente_id', 'El cliente no pertenece a la empresa autenticada.');
            }

            $servicioIds = collect($this->input('lineas', []))->pluck('servicio_id')->filter()->unique()->values();
            if ($servicioIds->isEmpty()) { return; }

            $validos = Servicio::query()->whereIn('id', $servicioIds)->where('empresa_id', $user->empresa_id)->pluck('id')->all();
            $invalidos = $servicioIds->diff($validos);
            if ($invalidos->isNotEmpty()) {
                $validator->errors()->add('lineas', 'Uno o más servicios no pertenecen a la empresa autenticada.');
            }
        });
    }
}
