<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreVerificacionUsuarioRequest;
use App\Http\Requests\Api\V1\UpdateVerificacionUsuarioRequest;
use App\Http\Resources\Api\V1\VerificacionUsuarioResource;
use App\Models\VerificacionUsuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function showMine(Request $request): JsonResponse
    {
        $record = VerificacionUsuario::query()
            ->where('user_id', $request->user()->id)
            ->first();

        if ($record === null) {
            return $this->notFound('No se encontró verificación para este usuario.');
        }

        return $this->success(VerificacionUsuarioResource::make($record)->resolve());
    }

    public function store(StoreVerificacionUsuarioRequest $request): JsonResponse
    {
        $userId = $request->user()->id;

        $exists = VerificacionUsuario::query()
            ->where('user_id', $userId)
            ->exists();

        if ($exists) {
            return $this->validationError([
                'user_id' => ['Ya existe una verificación de identidad para este usuario.'],
            ]);
        }

        $data = $request->validated();
        $data['user_id'] = $userId;

        $record = VerificacionUsuario::query()->create($data);

        return $this->created(VerificacionUsuarioResource::make($record)->resolve());
    }

    public function update(UpdateVerificacionUsuarioRequest $request, int $id): JsonResponse
    {
        $record = VerificacionUsuario::query()->find($id);

        if ($record === null) {
            return $this->notFound();
        }

        $record->fill($request->validated());
        $record->save();

        return $this->updated(VerificacionUsuarioResource::make($record)->resolve());
    }
}
