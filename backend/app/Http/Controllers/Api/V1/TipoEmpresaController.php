<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreTipoEmpresaRequest;
use App\Http\Requests\Api\V1\UpdateTipoEmpresaRequest;
use App\Http\Resources\Api\V1\TipoEmpresaResource;
use App\Models\AdminVerificacionEvento;
use App\Models\TipoEmpresa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TipoEmpresaController extends AbstractCrudController
{
    protected function modelClass(): string
    {
        return TipoEmpresa::class;
    }

    protected function resourceClass(): ?string
    {
        return TipoEmpresaResource::class;
    }

    public function store(StoreTipoEmpresaRequest $request): JsonResponse
    {
        $tipo_empresa = TipoEmpresa::query()->create($request->validated());

        $this->registrarEventoCatalogo($request, 'crear_tipo_empresa', null, 'creado', $tipo_empresa);

        return $this->created(
            TipoEmpresaResource::make($tipo_empresa)->resolve()
        );
    }

    public function update(UpdateTipoEmpresaRequest $request, int $id): JsonResponse
    {
        $tipo_empresa = TipoEmpresa::query()->find($id);

        if ($tipo_empresa === null) {
            return $this->notFound();
        }

        $estadoAnterior = $tipo_empresa->only(['nombre', 'descripcion']);
        $tipo_empresa->fill($request->validated());
        $tipo_empresa->save();

        $this->registrarEventoCatalogo($request, 'actualizar_tipo_empresa', 'actualizado', 'actualizado', $tipo_empresa, [
            'anterior' => $estadoAnterior,
            'nuevo' => $tipo_empresa->only(['nombre', 'descripcion']),
        ]);

        return $this->updated(
            TipoEmpresaResource::make($tipo_empresa)->resolve()
        );
    }

    private function registrarEventoCatalogo(Request $request, string $accion, ?string $estadoAnterior, ?string $estadoNuevo, TipoEmpresa $record, array $metadatos = []): void
    {
        AdminVerificacionEvento::query()->create([
            'user_admin_id' => $request->user()->id,
            'accion' => $accion,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'metadatos' => array_merge([
                'tipo_empresa_id' => $record->id,
                'nombre' => $record->nombre,
            ], $metadatos),
        ]);
    }
}
