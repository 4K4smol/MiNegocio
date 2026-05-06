<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreInformeRequest;
use App\Http\Requests\Api\V1\UpdateInformeRequest;
use App\Http\Resources\Api\V1\InformeResource;
use App\Models\Informe;
use Illuminate\Http\JsonResponse;

class InformeController extends AbstractCrudController
{
    protected function modelClass(): string
    {
        return Informe::class;
    }

    protected function resourceClass(): ?string
    {
        return InformeResource::class;
    }

    public function store(StoreInformeRequest $request): JsonResponse
    {
        $record = Informe::query()->create($request->validated());

        return $this->created(
            InformeResource::make($record)->resolve()
        );
    }

    public function update(UpdateInformeRequest $request, int $id): JsonResponse
    {
        $record = Informe::query()->find($id);

        if ($record === null) {
            return $this->notFound();
        }

        $record->fill($request->validated());
        $record->save();

        return $this->updated(
            InformeResource::make($record)->resolve()
        );
    }
}
