<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreOrdenTrabajoRequest;
use App\Http\Requests\Api\V1\UpdateOrdenTrabajoRequest;
use App\Http\Resources\Api\V1\FacturaResource;
use App\Http\Resources\Api\V1\OrdenTrabajoResource;
use App\Models\OrdenTrabajo;
use App\Services\FacturacionDesdeOrdenService;
use App\Services\OrdenTrabajoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrdenTrabajoController extends AbstractCrudController
{
    public function __construct(
        private readonly OrdenTrabajoService $ordenTrabajoService,
        private readonly FacturacionDesdeOrdenService $facturacionDesdeOrdenService,
    ) {}

    protected function modelClass(): string
    {
        return OrdenTrabajo::class;
    }

    protected function resourceClass(): ?string
    {
        return OrdenTrabajoResource::class;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->integer('per_page', 15), 1), 100);
        $items = $this->baseQuery($request)
            ->with(['cliente', 'estado', 'prioridad', 'tecnicoResponsable', 'lineas.servicio', 'eventosCalendario'])
            ->when($request->filled('estado'), fn ($query) => $query->where('estado_codigo', $request->string('estado')))
            ->when($request->filled('cliente_id'), fn ($query) => $query->where('cliente_id', $request->integer('cliente_id')))
            ->when($request->filled('desde'), fn ($query) => $query->whereDate('fecha_programada_inicio', '>=', $request->date('desde')))
            ->when($request->filled('hasta'), fn ($query) => $query->whereDate('fecha_programada_inicio', '<=', $request->date('hasta')))
            ->orderByDesc('fecha_programada_inicio')
            ->orderByDesc('created_at')
            ->paginate($perPage);
        return $this->success(OrdenTrabajoResource::collection($items)->response()->getData(true));
    }

    public function show(Request $request, int $orden): JsonResponse
    {
        $item = $this->baseQuery($request)
            ->with(['cliente', 'estado', 'prioridad', 'tecnicoResponsable', 'lineas.servicio', 'eventosCalendario'])
            ->whereKey($orden)
            ->first();
        if (!$item) {
            return $this->notFound();
        }
        return $this->success(OrdenTrabajoResource::make($item)->resolve());
    }

    public function store(StoreOrdenTrabajoRequest $request): JsonResponse
    {
        $orden = $this->ordenTrabajoService->crear($request->validated(), $request->user());
        return $this->created(OrdenTrabajoResource::make($orden)->resolve());
    }

    public function update(UpdateOrdenTrabajoRequest $request, OrdenTrabajo $orden): JsonResponse
    {
        if (!$this->findRecord($request, $orden->id)) {
            return $this->forbidden();
        }
        $updated = $this->ordenTrabajoService->actualizarOrden($orden, $request->validated(), $request->user());
        return $this->updated(OrdenTrabajoResource::make($updated)->resolve());
    }

    public function completar(Request $request, OrdenTrabajo $orden): JsonResponse
    {
        if (!$this->findRecord($request, $orden->id)) {
            return $this->forbidden();
        }
        return $this->updated(OrdenTrabajoResource::make($this->ordenTrabajoService->completarOrden($orden, $request->user()))->resolve(), 'Orden completada correctamente.');
    }

    public function cancelar(Request $request, OrdenTrabajo $orden): JsonResponse
    {
        if (!$this->findRecord($request, $orden->id)) {
            return $this->forbidden();
        }
        return $this->updated(OrdenTrabajoResource::make($this->ordenTrabajoService->cancelarOrden($orden, $request->user()))->resolve(), 'Orden cancelada correctamente.');
    }

    public function generarFactura(Request $request, OrdenTrabajo $orden): JsonResponse
    {
        if (!$this->findRecord($request, $orden->id)) {
            return $this->forbidden();
        }
        $modo = $request->string('modo', 'emitir')->toString();
        $factura = $this->facturacionDesdeOrdenService->generarDesdeOrden($orden->load(['empresa', 'cliente', 'estado', 'lineas.servicio']), $request->user(), $modo);
        return $this->created(FacturaResource::make($factura)->resolve(), 'Factura generada correctamente desde la orden.');
    }
}
