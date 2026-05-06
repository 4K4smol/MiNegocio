<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreRoleRequest;
use App\Http\Requests\Api\V1\UpdateRoleRequest;
use App\Http\Resources\Api\V1\RoleResource;
use App\Models\Role;
use Illuminate\Http\JsonResponse;

class RoleController extends AbstractCrudController
{
    protected function modelClass(): string
    {
        return Role::class;
    }

    protected function resourceClass(): ?string
    {
        return RoleResource::class;
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $record = Role::query()->create($request->validated());

        return $this->created(
            RoleResource::make($record)->resolve()
        );
    }

    public function update(UpdateRoleRequest $request, int $id): JsonResponse
    {
        $record = Role::query()->find($id);

        if ($record === null) {
            return $this->notFound();
        }

        $record->fill($request->validated());
        $record->save();

        return $this->updated(
            RoleResource::make($record)->resolve()
        );
    }
}
