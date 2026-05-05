<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreTipoDocumentoIdentidadRequest;
use App\Http\Requests\Api\V1\UpdateTipoDocumentoIdentidadRequest;
use App\Http\Resources\Api\V1\TipoDocumentoIdentidadResource;
use App\Models\TipoDocumentoIdentidad;
use Illuminate\Http\JsonResponse;

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
        $tipo_documento_identidad = TipoDocumentoIdentidad::query()
            ->create($request->validated());

        return $this->created(
            (new TipoDocumentoIdentidadResource($tipo_documento_identidad))->toArray($request)
        );
    }

    public function update(UpdateTipoDocumentoIdentidadRequest $request, int $id): JsonResponse
    {
        $tipo_documento_identidad = TipoDocumentoIdentidad::query()->find($id);

        if ($tipo_documento_identidad === null) {
            return $this->notFound();
        }

        $tipo_documento_identidad->fill($request->validated());
        $tipo_documento_identidad->save();

        return $this->updated(
            (new TipoDocumentoIdentidadResource($tipo_documento_identidad))->toArray($request)
        );
    }
}
