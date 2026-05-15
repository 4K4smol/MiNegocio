<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreTipoLocalizacionClienteRequest;
use App\Http\Requests\Api\V1\UpdateTipoLocalizacionClienteRequest;
use App\Http\Resources\Api\V1\TipoLocalizacionClienteResource;
use App\Models\AdminVerificacionEvento;
use App\Models\TipoLocalizacionCliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TipoLocalizacionClienteController extends AbstractCrudController
{
    protected function modelClass(): string
    {
        return TipoLocalizacionCliente::class;
    }

    protected function resourceClass(): ?string
    {
        return TipoLocalizacionClienteResource::class;
    }

    public function store(StoreTipoLocalizacionClienteRequest $request): JsonResponse
    {
        $tipo_localizacion_cliente = TipoLocalizacionCliente::query()->create($request->validated());

        $this->registrarEventoCatalogo($request, 'crear_tipo_localizacion_cliente', null, 'creado', $tipo_localizacion_cliente);

        return $this->created(
            TipoLocalizacionClienteResource::make($tipo_localizacion_cliente)->resolve()
        );
    }

    public function update(UpdateTipoLocalizacionClienteRequest $request, int $id): JsonResponse
    {
        $tipo_localizacion_cliente = TipoLocalizacionCliente::query()->find($id);

        if ($tipo_localizacion_cliente === null) {
            return $this->notFound();
        }

        $estadoAnterior = $tipo_localizacion_cliente->only(['codigo', 'nombre', 'descripcion', 'activo', 'orden']);
        $tipo_localizacion_cliente->fill($request->validated());
        $tipo_localizacion_cliente->save();

        $this->registrarEventoCatalogo($request, 'actualizar_tipo_localizacion_cliente', 'actualizado', 'actualizado', $tipo_localizacion_cliente, [
            'anterior' => $estadoAnterior,
            'nuevo' => $tipo_localizacion_cliente->only(['codigo', 'nombre', 'descripcion', 'activo', 'orden']),
        ]);

        return $this->updated(
            TipoLocalizacionClienteResource::make($tipo_localizacion_cliente)->resolve()
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
        $tipo_localizacion_cliente = TipoLocalizacionCliente::query()->find($id);

        if ($tipo_localizacion_cliente === null) {
            return $this->notFound();
        }

        $anterior = $tipo_localizacion_cliente->activo ? 'activo' : 'inactivo';
        $tipo_localizacion_cliente->update(['activo' => $activo]);

        $this->registrarEventoCatalogo(
            $request,
            $activo ? 'activar_tipo_localizacion_cliente' : 'desactivar_tipo_localizacion_cliente',
            $anterior,
            $activo ? 'activo' : 'inactivo',
            $tipo_localizacion_cliente->fresh(),
        );

        return $this->updated(TipoLocalizacionClienteResource::make($tipo_localizacion_cliente->fresh())->resolve(), 'Tipo de localizacion actualizado correctamente.');
    }

    private function registrarEventoCatalogo(Request $request, string $accion, ?string $estadoAnterior, ?string $estadoNuevo, TipoLocalizacionCliente $record, array $metadatos = []): void
    {
        AdminVerificacionEvento::query()->create([
            'user_admin_id' => $request->user()->id,
            'accion' => $accion,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'metadatos' => array_merge([
                'tipo_localizacion_cliente_id' => $record->id,
                'codigo' => $record->codigo,
            ], $metadatos),
        ]);
    }
}
