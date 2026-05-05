<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreEmpresaRequest;
use App\Http\Requests\Api\V1\UpdateEmpresaRequest;
use App\Http\Resources\Api\V1\EmpresaResource;
use App\Models\Empresa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmpresaController extends AbstractCrudController
{
    protected function modelClass(): string
    {
        return Empresa::class;
    }

    public function store(StoreEmpresaRequest $request): JsonResponse
    {
        $empresa = Empresa::query()->create($request->validated());

        return $this->created(EmpresaResource::make($empresa)->resolve());
    }

    public function update(UpdateEmpresaRequest $request, int $id): JsonResponse
    {
        $empresa = Empresa::query()->find($id);

        if ($empresa === null) {
            return $this->notFound();
        }

        $empresa->fill($request->validated());
        $empresa->save();

        return $this->updated(EmpresaResource::make($empresa)->resolve());
    }
}
