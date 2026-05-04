<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class AbstractCrudController extends ApiController
{
    abstract protected function modelClass(): string;

    public function index(Request $request): JsonResponse
    {
        /** @var Model $model */
        $model = new ($this->modelClass())();
        $perPage = (int) $request->integer('per_page', 15);

        return $this->success($model->newQuery()->paginate($perPage)->toArray());
    }

    public function show(int $id): JsonResponse
    {
        /** @var Model $model */
        $model = new ($this->modelClass())();
        $record = $model->newQuery()->find($id);

        if ($record === null) {
            return $this->notFound();
        }

        return $this->success($record->toArray());
    }

    public function destroy(int $id): JsonResponse
    {
        /** @var Model $model */
        $model = new ($this->modelClass())();
        $record = $model->newQuery()->find($id);

        if ($record === null) {
            return $this->notFound();
        }

        $record->delete();

        return $this->deleted();
    }
}
