<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreVerificacionUsuarioRequest;
use App\Http\Requests\Api\V1\UpdateVerificacionUsuarioRequest;
use App\Http\Resources\Api\V1\VerificacionUsuarioResource;
use App\Models\VerificacionUsuario;
use Illuminate\Http\JsonResponse;

class VerificacionUsuarioController extends AbstractCrudController
{
    protected function modelClass(): string
    {
        return VerificacionUsuario::class;
    }

    protected function resourceClass(): ?string
    {
        return VerificacionUsuarioResource::class;
    }

    public function store(StoreVerificacionUsuarioRequest $request): JsonResponse
    {
        $record = VerificacionUsuario::query()->create($request->validated());

        return $this->created(
            VerificacionUsuarioResource::make($record)->resolve()
        );
    }

    public function update(UpdateVerificacionUsuarioRequest $request, int $id): JsonResponse
    {
        $record = VerificacionUsuario::query()->find($id);

        if ($record === null) {
            return $this->notFound();
        }

        $record->fill($request->validated());
        $record->save();

        return $this->updated(
            VerificacionUsuarioResource::make($record)->resolve()
        );
    }
}
