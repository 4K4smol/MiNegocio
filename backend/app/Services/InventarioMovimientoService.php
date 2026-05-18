<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\InventarioItem;
use App\Models\InventarioMovimiento;
use App\Models\InventarioUbicacion;
use App\Models\TipoInventarioMovimiento;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventarioMovimientoService
{
    public function registrar(array $data, User $user): InventarioMovimiento
    {
        return DB::transaction(function () use ($data, $user): InventarioMovimiento {
            $empresaId = $this->empresaId($data, $user);

            $item = InventarioItem::query()
                ->whereKey($data['inventario_item_id'])
                ->where('empresa_id', $empresaId)
                ->lockForUpdate()
                ->first();

            if ($item === null) {
                throw ValidationException::withMessages([
                    'inventario_item_id' => ['El item de inventario no pertenece a la empresa indicada.'],
                ]);
            }

            $tipo = TipoInventarioMovimiento::query()
                ->whereKey($data['tipo_movimiento_id'])
                ->where('activo', true)
                ->first();

            if ($tipo === null) {
                throw ValidationException::withMessages([
                    'tipo_movimiento_id' => ['El tipo de movimiento no existe o no esta activo.'],
                ]);
            }

            $origenId = $this->validarUbicacion($data['ubicacion_origen_id'] ?? null, $empresaId, 'ubicacion_origen_id');
            $destinoId = $this->validarUbicacion($data['ubicacion_destino_id'] ?? null, $empresaId, 'ubicacion_destino_id');

            $stockAnterior = (float) $item->stock_actual;
            $cantidadEntrada = (float) $data['cantidad'];
            $cantidadMovimiento = $cantidadEntrada;
            $stockPosterior = $stockAnterior;

            match ($tipo->codigo) {
                'entrada' => $stockPosterior = $this->calcularEntrada($stockAnterior, $cantidadEntrada),
                'salida' => $stockPosterior = $this->calcularSalida($stockAnterior, $cantidadEntrada),
                'ajuste' => [$stockPosterior, $cantidadMovimiento] = $this->calcularAjuste($stockAnterior, $data),
                'traslado' => $this->validarTraslado($origenId, $destinoId, $cantidadEntrada),
                default => throw ValidationException::withMessages([
                    'tipo_movimiento_id' => ['Tipo de movimiento de inventario no soportado.'],
                ]),
            };

            if ($tipo->codigo !== 'traslado') {
                $item->stock_actual = $stockPosterior;
            } elseif ($destinoId !== null) {
                $item->ubicacion_id = $destinoId;
            }

            $item->save();

            return InventarioMovimiento::query()
                ->create([
                    'empresa_id' => $empresaId,
                    'inventario_item_id' => $item->id,
                    'ubicacion_origen_id' => $origenId,
                    'ubicacion_destino_id' => $destinoId,
                    'tipo_movimiento_id' => $tipo->id,
                    'cantidad' => $cantidadMovimiento,
                    'stock_anterior' => $stockAnterior,
                    'stock_posterior' => $stockPosterior,
                    'motivo' => $data['motivo'] ?? null,
                    'fecha_movimiento' => $data['fecha_movimiento'] ?? now(),
                    'user_id' => $user->id,
                ])
                ->load(['item.unidadMedida', 'item.ubicacion', 'tipoMovimiento', 'ubicacionOrigen', 'ubicacionDestino', 'user']);
        });
    }

    private function empresaId(array $data, User $user): int
    {
        if ($this->esAdmin($user)) {
            return (int) $data['empresa_id'];
        }

        return (int) $user->empresa_id;
    }

    private function calcularEntrada(float $stockAnterior, float $cantidad): float
    {
        $this->validarCantidadPositiva($cantidad, 'cantidad');

        return $stockAnterior + $cantidad;
    }

    private function calcularSalida(float $stockAnterior, float $cantidad): float
    {
        $this->validarCantidadPositiva($cantidad, 'cantidad');

        $stockPosterior = $stockAnterior - $cantidad;

        if ($stockPosterior < 0) {
            throw ValidationException::withMessages([
                'cantidad' => ['La salida no puede dejar el stock en negativo.'],
            ]);
        }

        return $stockPosterior;
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function calcularAjuste(float $stockAnterior, array $data): array
    {
        $stockPosterior = array_key_exists('stock_posterior', $data) && $data['stock_posterior'] !== null
            ? (float) $data['stock_posterior']
            : (float) $data['cantidad'];

        if ($stockPosterior < 0) {
            throw ValidationException::withMessages([
                'stock_posterior' => ['El stock ajustado no puede ser negativo.'],
            ]);
        }

        return [$stockPosterior, $stockPosterior - $stockAnterior];
    }

    private function validarTraslado(?int $origenId, ?int $destinoId, float $cantidad): void
    {
        $this->validarCantidadPositiva($cantidad, 'cantidad');

        if ($origenId === null || $destinoId === null) {
            throw ValidationException::withMessages([
                'ubicacion_destino_id' => ['El traslado requiere ubicacion de origen y destino.'],
            ]);
        }

        if ($origenId === $destinoId) {
            throw ValidationException::withMessages([
                'ubicacion_destino_id' => ['La ubicacion de destino debe ser distinta de la de origen.'],
            ]);
        }
    }

    private function validarCantidadPositiva(float $cantidad, string $field): void
    {
        if ($cantidad <= 0) {
            throw ValidationException::withMessages([
                $field => ['La cantidad debe ser mayor que cero.'],
            ]);
        }
    }

    private function validarUbicacion(mixed $id, int $empresaId, string $field): ?int
    {
        if ($id === null || $id === '') {
            return null;
        }

        $ubicacionId = (int) $id;
        $existe = InventarioUbicacion::query()
            ->whereKey($ubicacionId)
            ->where('empresa_id', $empresaId)
            ->exists();

        if (!$existe) {
            throw ValidationException::withMessages([
                $field => ['La ubicacion no pertenece a la empresa indicada.'],
            ]);
        }

        return $ubicacionId;
    }

    private function esAdmin(User $user): bool
    {
        return $user->role?->nombre === 'admin';
    }
}
