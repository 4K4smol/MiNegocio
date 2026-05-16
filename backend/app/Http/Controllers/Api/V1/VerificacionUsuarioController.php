<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreVerificacionUsuarioRequest;
use App\Http\Requests\Api\V1\UpdateVerificacionUsuarioRequest;
use App\Http\Resources\Api\V1\VerificacionUsuarioResource;
use App\Models\EstadoVerificacion;
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

        $estadoPendienteId = EstadoVerificacion::query()
            ->where('nombre', 'pendiente')
            ->value('id');

        if ($estadoPendienteId === null) {
            return $this->error('No se encontró el estado de verificación pendiente.', 500);
        }

        $data = $request->validated();
        $data['user_id'] = $userId;
        $data['estado_verificacion_id'] = $estadoPendienteId;

        $record = VerificacionUsuario::query()->create($data);

        return $this->created(VerificacionUsuarioResource::make($record)->resolve());
    }

    public function update(UpdateVerificacionUsuarioRequest $request, int $id): JsonResponse
    {
        $record = VerificacionUsuario::query()->find($id);

        if ($record === null) {
            return $this->notFound();
        }

        $data = $request->validated();
        $data['revisado_por'] = $request->user()->id;

        if (isset($data['estado_verificacion_id']) && ! array_key_exists('fecha_verificacion', $data)) {
            $aprobadoId = EstadoVerificacion::query()
                ->where('nombre', 'aprobada')
                ->value('id');

            if ($aprobadoId !== null && (int) $data['estado_verificacion_id'] === (int) $aprobadoId) {
                $data['fecha_verificacion'] = now();
            }
        }

        $record->fill($data);
        $record->save();

        return $this->updated(VerificacionUsuarioResource::make($record)->resolve());
    }
}
