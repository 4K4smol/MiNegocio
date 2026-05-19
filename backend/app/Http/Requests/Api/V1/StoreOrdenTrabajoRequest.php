<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\Cliente;
use App\Models\Servicio;
use App\Models\ServicioPrecio;
use App\Models\TipoTarifaServicio;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreOrdenTrabajoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'prioridad_id' => ['nullable', 'integer', 'exists:orden_trabajo_prioridades,id'],
            'prioridad_codigo' => ['nullable', 'string', 'exists:orden_trabajo_prioridades,codigo'],
            'fecha_programada' => ['nullable', 'date'],
            'fecha_programada_inicio' => ['nullable', 'date'],
            'fecha_programada_fin' => ['nullable', 'date', 'after_or_equal:fecha_programada_inicio'],
            'tecnico_responsable_id' => ['nullable', 'integer', 'exists:users,id'],
            'observaciones' => ['nullable', 'string'],
            'notas_cliente' => ['nullable', 'string'],
            'notas_internas' => ['nullable', 'string'],
            'lineas' => ['required', 'array', 'min:1'],
            'lineas.*.servicio_id' => ['required', 'integer', 'exists:servicios,id'],
            'lineas.*.servicio_precio_id' => ['nullable', 'integer', 'exists:servicio_precios,id'],
            'lineas.*.tipo_tarifa_servicio_id' => [
                'nullable',
                'integer',
                Rule::exists('tipos_tarifa_servicio', 'id')->where(fn ($query) => $query->where('activo', true)),
            ],
            'lineas.*.descripcion' => ['nullable', 'string'],
            'lineas.*.observaciones' => ['nullable', 'string'],
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
            if ($user === null || $user->role?->nombre === 'admin') {
                return;
            }

            $orden = $this->route('orden');
            $clienteId = (int) ($this->input('cliente_id') ?: $orden?->cliente_id);
            $clienteOk = Cliente::query()
                ->whereKey($clienteId)
                ->where('empresa_id', $user->empresa_id)
                ->exists();

            if (!$clienteOk) {
                $validator->errors()->add('cliente_id', 'El cliente no pertenece a la empresa autenticada.');
            }

            $servicioIds = collect($this->input('lineas', []))->pluck('servicio_id')->filter()->unique()->values();
            if ($servicioIds->isNotEmpty()) {
                $validos = Servicio::query()
                    ->whereIn('id', $servicioIds)
                    ->where('empresa_id', $user->empresa_id)
                    ->pluck('id')
                    ->all();

                if ($servicioIds->diff($validos)->isNotEmpty()) {
                    $validator->errors()->add('lineas', 'Uno o más servicios no pertenecen a la empresa autenticada.');
                }
            }

            foreach ($this->input('lineas', []) as $index => $linea) {
                $servicioId = (int) ($linea['servicio_id'] ?? 0);
                $precioId = (int) ($linea['servicio_precio_id'] ?? 0);
                $tipoTarifaId = (int) ($linea['tipo_tarifa_servicio_id'] ?? 0);

                if ($precioId > 0) {
                    $precioOk = ServicioPrecio::query()
                        ->whereKey($precioId)
                        ->where('servicio_id', $servicioId)
                        ->whereHas('servicio', fn ($query) => $query->where('empresa_id', $user->empresa_id))
                        ->exists();

                    if (!$precioOk) {
                        $validator->errors()->add("lineas.{$index}.servicio_precio_id", 'El precio seleccionado no pertenece al servicio indicado.');
                    }
                }

                if ($tipoTarifaId > 0) {
                    $tipoTarifaOk = TipoTarifaServicio::query()
                        ->whereKey($tipoTarifaId)
                        ->where('activo', true)
                        ->exists();

                    if (!$tipoTarifaOk) {
                        $validator->errors()->add("lineas.{$index}.tipo_tarifa_servicio_id", 'El tipo de tarifa seleccionado no está activo.');
                    }
                }
            }
        });
    }
}
