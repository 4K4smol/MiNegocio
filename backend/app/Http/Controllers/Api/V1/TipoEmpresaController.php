<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreTipoEmpresaRequest;
use App\Http\Requests\Api\V1\UpdateTipoEmpresaRequest;
use App\Http\Resources\Api\V1\TipoEmpresaResource;
use App\Models\TipoEmpresa;

class TipoEmpresaController extends AbstractCrudController
{
    protected function modelClass(): string
    {
        return TipoEmpresa::class;
    }

    public function store(StoreTipoEmpresaRequest $request)
    {
        $tipo_empresa = TipoEmpresa::query()->create($request->validated());

        return $this->created((new TipoEmpresaResource($tipo_empresa))->toArray($request));
    }

    public function update(UpdateTipoEmpresaRequest $request, int $id)
    {
        $tipo_empresa = TipoEmpresa::query()->find($id);

        if ($tipo_empresa === null) {
            return $this->notFound();
        }

        $tipo_empresa->fill($request->validated());
        $tipo_empresa->save();

        return $this->updated((new TipoEmpresaResource($tipo_empresa))->toArray($request));
    }
}
