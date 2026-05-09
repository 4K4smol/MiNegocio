<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

abstract class AbstractCrudController extends ApiController
{
    private const ROL_ADMIN = 'admin';
    private const ROL_TITULAR = 'titular';

    abstract protected function modelClass(): string;

    protected function resourceClass(): ?string
    {
        return null;
    }

    public function index(Request $request): JsonResponse
    {
        $per_page = (int) $request->integer('per_page', 15);
        $per_page = min(max($per_page, 1), 100);

        $records = $this->baseQuery($request)->paginate($per_page);

        $resourceClass = $this->resourceClass();

        if ($resourceClass !== null) {
            return $this->success($resourceClass::collection($records)->response()->getData(true));
        }

        return $this->success($records->toArray());
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $record = $this->findRecord($request, $id);

        if ($record === null) {
            return $this->notFound();
        }

        $resourceClass = $this->resourceClass();

        if ($resourceClass !== null) {
            return $this->success((new $resourceClass($record))->toArray($request));
        }

        return $this->success($record->toArray());
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $record = $this->findRecord($request, $id);

        if ($record === null) {
            return $this->notFound();
        }

        $record->delete();

        return $this->deleted();
    }

    protected function fillEmpresaIdFromUser(array $data, Request $request): array
    {
        if (! $this->usaEmpresaId()) {
            return $data;
        }

        $user = $request->user();

        if (! $user instanceof User || $this->esAdministrador($user)) {
            return $data;
        }

        $data['empresa_id'] = $user->empresa_id;

        return $data;
    }

    protected function findRecord(Request $request, int $id): ?Model
    {
        return $this->baseQuery($request)->whereKey($id)->first();
    }

    protected function baseQuery(Request $request): Builder
    {
        $modelClass = $this->modelClass();
        $query = $modelClass::query();

        if (! $this->usaEmpresaId()) {
            return $query;
        }

        $user = $request->user();

        if (! $user instanceof User || $this->esAdministrador($user)) {
            return $query;
        }

        return $query->where('empresa_id', $user->empresa_id);
    }

    protected function esAdministrador(User $user): bool
    {
        return $user->role?->nombre === self::ROL_ADMIN;
    }

    protected function esTitular(User $user): bool
    {
        return $user->role?->nombre === self::ROL_TITULAR;
    }

    protected function perteneceAEmpresaDelUsuario(Model $modelo, User $user): bool
    {
        return (int) ($modelo->getAttribute('empresa_id') ?? 0) === (int) $user->empresa_id;
    }

    private function usaEmpresaId(): bool
    {
        $model = new ($this->modelClass())();

        return Schema::hasColumn($model->getTable(), 'empresa_id');
    }
}
