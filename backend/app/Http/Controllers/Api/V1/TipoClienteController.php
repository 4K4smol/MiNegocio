<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreTipoClienteRequest;
use App\Http\Requests\Api\V1\UpdateTipoClienteRequest;
use App\Http\Resources\Api\V1\TipoClienteResource;
use App\Models\AdminVerificacionEvento;
use App\Models\TipoCliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TipoClienteController extends AbstractCrudController
{
    protected function modelClass(): string
    {
        return TipoCliente::class;
    }

    protected function resourceClass(): ?string
    {
        return TipoClienteResource::class;
    }

    public function store(StoreTipoClienteRequest $request): JsonResponse
    {
        $tipo_cliente = TipoCliente::query()->create($request->validated());

        $this->registrarEventoCatalogo($request, 'crear_tipo_cliente', null, 'creado', $tipo_cliente);

        return $this->created(
            TipoClienteResource::make($tipo_cliente)->resolve()
        );
    }

    public function update(UpdateTipoClienteRequest $request, int $id): JsonResponse
    {
        $tipo_cliente = TipoCliente::query()->find($id);

        if ($tipo_cliente === null) {
            return $this->notFound();
        }

        $estadoAnterior = $tipo_cliente->only(['codigo', 'nombre', 'descripcion', 'activo', 'orden']);
        $tipo_cliente->fill($request->validated());
        $tipo_cliente->save();

        $this->registrarEventoCatalogo($request, 'actualizar_tipo_cliente', 'actualizado', 'actualizado', $tipo_cliente, [
            'anterior' => $estadoAnterior,
            'nuevo' => $tipo_cliente->only(['codigo', 'nombre', 'descripcion', 'activo', 'orden']),
        ]);

        return $this->updated(
            TipoClienteResource::make($tipo_cliente)->resolve()
        );
    }

    public function activar(Request $request, int $id): JsonResponse
    {
        return $this->actualizarActivo($request, $id, true);
    }

    public function desactivar(Request $request, int $id): JsonResponse
    {
        return $this->actualizarActivo($request, $id, false);
    }

    private function actualizarActivo(Request $request, int $id, bool $activo): JsonResponse
    {
        $tipo_cliente = TipoCliente::query()->find($id);

        if ($tipo_cliente === null) {
            return $this->notFound();
        }

        $anterior = $tipo_cliente->activo ? 'activo' : 'inactivo';
        $tipo_cliente->update(['activo' => $activo]);

        $this->registrarEventoCatalogo(
            $request,
            $activo ? 'activar_tipo_cliente' : 'desactivar_tipo_cliente',
            $anterior,
            $activo ? 'activo' : 'inactivo',
            $tipo_cliente->fresh(),
        );

        return $this->updated(TipoClienteResource::make($tipo_cliente->fresh())->resolve(), 'Tipo de cliente actualizado correctamente.');
    }

    private function registrarEventoCatalogo(Request $request, string $accion, ?string $estadoAnterior, ?string $estadoNuevo, TipoCliente $record, array $metadatos = []): void
    {
        AdminVerificacionEvento::query()->create([
            'user_admin_id' => $request->user()->id,
            'accion' => $accion,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'metadatos' => array_merge([
                'tipo_cliente_id' => $record->id,
                'codigo' => $record->codigo,
            ], $metadatos),
        ]);
    }
}
