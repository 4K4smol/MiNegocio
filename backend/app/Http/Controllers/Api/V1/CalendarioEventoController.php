<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreCalendarioEventoRequest;
use App\Http\Requests\Api\V1\UpdateCalendarioEventoRequest;
use App\Http\Resources\Api\V1\CalendarioEventoResource;
use App\Models\CalendarioEvento;
use App\Services\CalendarioEventoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarioEventoController extends AbstractCrudController
{
    public function __construct(private readonly CalendarioEventoService $calendarioEventoService) {}

    protected function modelClass(): string
    {
        return CalendarioEvento::class;
    }

    protected function resourceClass(): ?string
    {
        return CalendarioEventoResource::class;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->integer('per_page', 15), 1), 100);
        $query = $this->baseQuery($request)
            ->with(['cliente', 'ordenTrabajo', 'creador'])
            ->orderBy('inicio');

        if ($request->filled('desde')) {
            $query->where('inicio', '>=', $request->input('desde'));
        }

        if ($request->filled('hasta')) {
            $query->where(function ($query) use ($request): void {
                $query->where('inicio', '<=', $request->input('hasta'))
                    ->orWhere('fin', '<=', $request->input('hasta'));
            });
        }

        $items = $query->paginate($perPage);

        return $this->success(CalendarioEventoResource::collection($items)->response()->getData(true));
    }

    public function show(Request $request, int $calendarioEvento): JsonResponse
    {
        $item = $this->baseQuery($request)
            ->with(['cliente', 'ordenTrabajo', 'creador'])
            ->whereKey($calendarioEvento)
            ->first();

        if (! $item) {
            return $this->notFound();
        }

        return $this->success(CalendarioEventoResource::make($item)->resolve());
    }

    public function store(StoreCalendarioEventoRequest $request): JsonResponse
    {
        $evento = $this->calendarioEventoService->crearEvento($request->validated(), $request->user());

        return $this->created(CalendarioEventoResource::make($evento)->resolve());
    }

    public function update(UpdateCalendarioEventoRequest $request, int $calendarioEvento): JsonResponse
    {
        $eventoActual = $this->findRecord($request, $calendarioEvento);
        if (! $eventoActual instanceof CalendarioEvento) {
            return $this->forbidden();
        }

        $evento = $this->calendarioEventoService->actualizarEvento($eventoActual, $request->validated(), $request->user());

        return $this->updated(CalendarioEventoResource::make($evento)->resolve());
    }

    public function destroy(Request $request, int $calendarioEvento): JsonResponse
    {
        $evento = $this->findRecord($request, $calendarioEvento);
        if (! $evento) {
            return $this->notFound();
        }

        $evento->delete();

        return $this->deleted();
    }
}
