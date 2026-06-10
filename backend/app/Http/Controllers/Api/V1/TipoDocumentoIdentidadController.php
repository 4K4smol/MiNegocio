<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreTipoDocumentoIdentidadRequest;
use App\Http\Requests\Api\V1\UpdateTipoDocumentoIdentidadRequest;
use App\Http\Resources\Api\V1\TipoDocumentoIdentidadResource;
use App\Models\AdminVerificacionEvento;
use App\Models\TipoDocumentoIdentidad;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TipoDocumentoIdentidadController extends AbstractCrudController
{
    protected function modelClass(): string
    {
        return TipoDocumentoIdentidad::class;
    }

    protected function resourceClass(): ?string
    {
        return TipoDocumentoIdentidadResource::class;
    }

    public function store(StoreTipoDocumentoIdentidadRequest $request): JsonResponse
    {
        $tipo_documento_identidad = DB::transaction(function () use ($request): TipoDocumentoIdentidad {
            $tipo_documento_identidad = TipoDocumentoIdentidad::query()
                ->create($request->validated());

            $this->registrarEventoCatalogo($request, 'crear_tipo_documento_identidad', null, 'creado', $tipo_documento_identidad);

            return $tipo_documento_identidad;
        });

        return $this->created(
            TipoDocumentoIdentidadResource::make($tipo_documento_identidad)->resolve()
        );
    }

    public function update(UpdateTipoDocumentoIdentidadRequest $request, int $id): JsonResponse
    {
        $tipo_documento_identidad = DB::transaction(function () use ($request, $id): ?TipoDocumentoIdentidad {
            $tipo_documento_identidad = TipoDocumentoIdentidad::query()->find($id);

            if ($tipo_documento_identidad === null) {
                return null;
            }

            $estadoAnterior = $tipo_documento_identidad->only(['nombre', 'descripcion']);
            $tipo_documento_identidad->fill($request->validated());
            $tipo_documento_identidad->save();

            $this->registrarEventoCatalogo($request, 'actualizar_tipo_documento_identidad', 'actualizado', 'actualizado', $tipo_documento_identidad, [
                'anterior' => $estadoAnterior,
                'nuevo' => $tipo_documento_identidad->only(['nombre', 'descripcion']),
            ]);

            return $tipo_documento_identidad;
        });

        if ($tipo_documento_identidad === null) {
            return $this->notFound();
        }

        return $this->updated(
            TipoDocumentoIdentidadResource::make($tipo_documento_identidad)->resolve()
        );
    }

    private function registrarEventoCatalogo(Request $request, string $accion, ?string $estadoAnterior, ?string $estadoNuevo, TipoDocumentoIdentidad $record, array $metadatos = []): void
    {
        AdminVerificacionEvento::query()->create([
            'user_admin_id' => $request->user()->id,
            'accion' => $accion,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'metadatos' => array_merge([
                'tipo_documento_identidad_id' => $record->id,
                'nombre' => $record->nombre,
            ], $metadatos),
        ]);
    }
}
