<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\FacturaResource;
use App\Models\EstadoFactura;
use App\Models\Factura;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class FacturaController extends AbstractCrudController
{
    protected function modelClass(): string
    {
        return Factura::class;
    }

    protected function resourceClass(): ?string
    {
        return FacturaResource::class;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->integer('per_page', 15), 1), 100);
        $items = $this->baseQuery($request)
            ->with(['lineas', 'impuestos', 'cliente', 'empresa', 'estadoFactura', 'tipoFactura'])
            ->paginate($perPage);

        return $this->success(FacturaResource::collection($items)->response()->getData(true));
    }

    public function show(Request $request, int $factura): JsonResponse
    {
        $item = $this->baseQuery($request)
            ->with(['lineas', 'impuestos', 'cliente', 'empresa', 'estadoFactura', 'tipoFactura'])
            ->whereKey($factura)
            ->first();

        if (! $item) {
            return $this->notFound();
        }

        return $this->success(FacturaResource::make($item)->resolve());
    }

    public function marcarPagada(Request $request, Factura $factura): JsonResponse
    {
        if (! $this->findRecord($request, $factura->id)) {
            return $this->forbidden();
        }

        $estadoPagada = EstadoFactura::query()->where('codigo', 'pagada')->first();
        if (! $estadoPagada) {
            throw new RuntimeException('No existe el estado de factura "pagada". Ejecuta los seeders de estados de factura.');
        }

        $factura->estado_factura_id = $estadoPagada->id;
        $factura->pagada = true;
        $factura->fecha_pago = now()->toDateString();
        $factura->save();

        return $this->updated(FacturaResource::make($factura->fresh(['lineas', 'impuestos', 'cliente', 'empresa', 'estadoFactura', 'tipoFactura']))->resolve(), 'Factura marcada como pagada.');
    }

    public function anular(Request $request, Factura $factura): JsonResponse
    {
        if (! $this->findRecord($request, $factura->id)) {
            return $this->forbidden();
        }

        $estadoAnulada = EstadoFactura::query()->where('codigo', 'anulada')->first();
        if (! $estadoAnulada) {
            throw new RuntimeException('No existe el estado de factura "anulada". Ejecuta los seeders de estados de factura.');
        }

        $factura->estado_factura_id = $estadoAnulada->id;
        $factura->save();

        return $this->updated(FacturaResource::make($factura->fresh(['lineas', 'impuestos', 'cliente', 'empresa', 'estadoFactura', 'tipoFactura']))->resolve(), 'Factura anulada correctamente.');
    }
}
