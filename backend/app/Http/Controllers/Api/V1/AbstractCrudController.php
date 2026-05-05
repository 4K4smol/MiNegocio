<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class AbstractCrudController extends ApiController
{
    abstract protected function modelClass(): string;

    protected function resourceClass(): ?string
    {
        return null;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 15);
        $perPage = min(max($perPage, 1), 100);

        $modelClass = $this->modelClass();

        $records = $modelClass::query()
            ->paginate($perPage);

        $resourceClass = $this->resourceClass();

        if ($resourceClass !== null) {
            return $this->success(
                $resourceClass::collection($records)
                    ->response()
                    ->getData(true)
            );
        }

        return $this->success($records->toArray());
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $modelClass = $this->modelClass();

        /** @var Model|null $record */
        $record = $modelClass::query()->find($id);

        if ($record === null) {
            return $this->notFound();
        }

        $resourceClass = $this->resourceClass();

        if ($resourceClass !== null) {
            return $this->success(
                (new $resourceClass($record))->toArray($request)
            );
        }

        return $this->success($record->toArray());
    }

    public function destroy(int $id): JsonResponse
    {
        $modelClass = $this->modelClass();

        /** @var Model|null $record */
        $record = $modelClass::query()->find($id);

        if ($record === null) {
            return $this->notFound();
        }

        $record->delete();

        return $this->deleted();
    }
}
