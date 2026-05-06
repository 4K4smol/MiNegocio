<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreTareaRequest;
use App\Http\Requests\Api\V1\UpdateTareaRequest;
use App\Http\Resources\Api\V1\TareaResource;
use App\Models\Tarea;
use Illuminate\Http\JsonResponse;

class TareaController extends AbstractCrudController
{
    protected function modelClass(): string
    {
        return Tarea::class;
    }

    protected function resourceClass(): ?string
    {
        return TareaResource::class;
    }

    public function store(StoreTareaRequest $request): JsonResponse
    {
        $record = Tarea::query()->create($request->validated());

        return $this->created(
            TareaResource::make($record)->resolve()
        );
    }

    public function update(UpdateTareaRequest $request, int $id): JsonResponse
    {
        $record = Tarea::query()->find($id);

        if ($record === null) {
            return $this->notFound();
        }

        $record->fill($request->validated());
        $record->save();

        return $this->updated(
            TareaResource::make($record)->resolve()
        );
    }
}
