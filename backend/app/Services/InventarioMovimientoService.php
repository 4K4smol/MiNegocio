<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\InventarioItem;
use App\Models\InventarioExistencia;
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

            [$stockPosterior, $cantidadMovimiento] = match ($tipo->codigo) {
                'entrada' => $this->registrarEntrada($item, $destinoId, $cantidadEntrada),
                'salida' => $this->registrarSalida($item, $origenId, $cantidadEntrada),
                'ajuste' => $this->registrarAjuste($item, $origenId ?? $destinoId, $data),
                'traslado' => $this->registrarTraslado($item, $origenId, $destinoId, $cantidadEntrada),
                default => throw ValidationException::withMessages([
                    'tipo_movimiento_id' => ['Tipo de movimiento de inventario no soportado.'],
                ]),
            };

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
                ->load(['item.unidadMedida', 'item.ubicacion', 'item.existencias.ubicacion', 'tipoMovimiento', 'ubicacionOrigen', 'ubicacionDestino', 'user']);
        });
    }

    private function empresaId(array $data, User $user): int
    {
        if ($this->esAdmin($user)) {
            return (int) $data['empresa_id'];
        }

        return (int) $user->empresa_id;
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function registrarEntrada(InventarioItem $item, ?int $destinoId, float $cantidad): array
    {
        $this->validarUbicacionRequerida($destinoId, 'ubicacion_destino_id', 'La entrada requiere ubicacion de destino.');
        $this->validarCantidadPositiva($cantidad, 'cantidad');

        $existencia = $this->existenciaParaActualizar($item, $destinoId, true);
        $existencia->cantidad = (float) $existencia->cantidad + $cantidad;
        $existencia->save();

        return [$this->sincronizarStockTotal($item), $cantidad];
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function registrarSalida(InventarioItem $item, ?int $origenId, float $cantidad): array
    {
        $this->validarUbicacionRequerida($origenId, 'ubicacion_origen_id', 'La salida requiere ubicacion de origen.');
        $this->validarCantidadPositiva($cantidad, 'cantidad');

        $existencia = $this->existenciaParaActualizar($item, $origenId, false);
        $stockUbicacion = (float) ($existencia?->cantidad ?? 0);

        if ($stockUbicacion - $cantidad < 0) {
            throw ValidationException::withMessages([
                'cantidad' => ['La salida no puede dejar el stock de la ubicacion en negativo.'],
            ]);
        }

        $existencia->cantidad = $stockUbicacion - $cantidad;
        $existencia->save();

        return [$this->sincronizarStockTotal($item), $cantidad];
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function registrarAjuste(InventarioItem $item, ?int $ubicacionId, array $data): array
    {
        $this->validarUbicacionRequerida($ubicacionId, 'ubicacion_origen_id', 'El ajuste requiere ubicacion.');

        $stockUbicacionPosterior = array_key_exists('stock_posterior', $data) && $data['stock_posterior'] !== null
            ? (float) $data['stock_posterior']
            : (float) $data['cantidad'];

        if ($stockUbicacionPosterior < 0) {
            throw ValidationException::withMessages([
                'stock_posterior' => ['El stock ajustado no puede ser negativo.'],
            ]);
        }

        $existencia = $this->existenciaParaActualizar($item, $ubicacionId, true);
        $stockUbicacionAnterior = (float) $existencia->cantidad;
        $existencia->cantidad = $stockUbicacionPosterior;
        $existencia->save();

        return [$this->sincronizarStockTotal($item), $stockUbicacionPosterior - $stockUbicacionAnterior];
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function registrarTraslado(InventarioItem $item, ?int $origenId, ?int $destinoId, float $cantidad): array
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

        $origen = $this->existenciaParaActualizar($item, $origenId, false);
        $stockOrigen = (float) ($origen?->cantidad ?? 0);

        if ($stockOrigen - $cantidad < 0) {
            throw ValidationException::withMessages([
                'cantidad' => ['El traslado no puede dejar el stock de origen en negativo.'],
            ]);
        }

        $destino = $this->existenciaParaActualizar($item, $destinoId, true);

        $origen->cantidad = $stockOrigen - $cantidad;
        $origen->save();

        $destino->cantidad = (float) $destino->cantidad + $cantidad;
        $destino->save();

        return [$this->sincronizarStockTotal($item), $cantidad];
    }

    private function validarUbicacionRequerida(?int $ubicacionId, string $field, string $message): void
    {
        if ($ubicacionId === null) {
            throw ValidationException::withMessages([
                $field => [$message],
            ]);
        }
    }

    private function existenciaParaActualizar(InventarioItem $item, int $ubicacionId, bool $crear): ?InventarioExistencia
    {
        $existencia = InventarioExistencia::query()
            ->where('inventario_item_id', $item->id)
            ->where('ubicacion_id', $ubicacionId)
            ->lockForUpdate()
            ->first();

        if ($existencia !== null || !$crear) {
            return $existencia;
        }

        return InventarioExistencia::query()->create([
            'empresa_id' => $item->empresa_id,
            'inventario_item_id' => $item->id,
            'ubicacion_id' => $ubicacionId,
            'cantidad' => 0,
        ]);
    }

    private function sincronizarStockTotal(InventarioItem $item): float
    {
        $stockTotal = (float) InventarioExistencia::query()
            ->where('inventario_item_id', $item->id)
            ->sum('cantidad');

        $ubicacionPrincipal = InventarioExistencia::query()
            ->where('inventario_item_id', $item->id)
            ->where('cantidad', '>', 0)
            ->orderByDesc('cantidad')
            ->orderBy('ubicacion_id')
            ->first();

        $item->stock_actual = $stockTotal;
        $item->ubicacion_id = $ubicacionPrincipal?->ubicacion_id;
        $item->save();

        return $stockTotal;
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
