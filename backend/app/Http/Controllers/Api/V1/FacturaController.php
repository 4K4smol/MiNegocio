<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\FacturaResource;
use App\Models\EstadoFactura;
use App\Models\Factura;
use App\Services\RegistroFacturacionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class FacturaController extends AbstractCrudController
{
    public function __construct(private readonly RegistroFacturacionService $registroFacturacionService) {}

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
            ->with(['lineas', 'impuestos', 'cliente', 'empresa', 'estadoFactura', 'tipoFactura', 'registrosFacturacion'])
            ->paginate($perPage);

        return $this->success(FacturaResource::collection($items)->response()->getData(true));
    }

    public function show(Request $request, int $factura): JsonResponse
    {
        $item = $this->baseQuery($request)
            ->with(['lineas', 'impuestos', 'cliente', 'empresa', 'estadoFactura', 'tipoFactura', 'registrosFacturacion'])
            ->whereKey($factura)
            ->first();

        if (!$item) {
            return $this->notFound();
        }

        return $this->success(FacturaResource::make($item)->resolve());
    }

    public function marcarPagada(Request $request, Factura $factura): JsonResponse
    {
        if (!$this->findRecord($request, $factura->id)) {
            return $this->forbidden();
        }

        $estado_pagada = EstadoFactura::query()->where('codigo', 'pagada')->first();
        if (!$estado_pagada) {
            throw new RuntimeException('No existe el estado de factura "pagada". Ejecuta los seeders de estados de factura.');
        }

        if ($factura->estadoFactura?->codigo === 'anulada') {
            throw new RuntimeException('Una factura anulada no puede marcarse como pagada.');
        }

        $factura->estado_factura_id = $estado_pagada->id;
        $factura->pagada = true;
        $factura->fecha_pago = now()->toDateString();
        $factura->save();

        return $this->updated(
            FacturaResource::make(
                $factura->fresh([
                    'lineas',
                    'impuestos',
                    'cliente',
                    'empresa',
                    'estadoFactura',
                    'tipoFactura',
                    'registrosFacturacion'
                ])
            )->resolve(),
            'Factura marcada como pagada.'
        );
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

        DB::transaction(function () use ($factura, $estadoAnulada, $request): void {
            $factura->estado_factura_id = $estadoAnulada->id;
            $factura->observaciones = trim((string) $request->input('motivo_anulacion', '')) ?: $factura->observaciones;
            $factura->save();

            $this->registroFacturacionService->generarRegistroAnulacion($factura, (string) $request->input('motivo_anulacion', 'Anulación de factura'));
            $this->registroFacturacionService->registrarEvento((int) $factura->empresa_id, 'FACTURA_ANULADA', 'Factura anulada.');
            $this->registroFacturacionService->registrarEvento((int) $factura->empresa_id, 'REGISTRO_FACTURACION_ANULACION_GENERADO', 'Registro de anulación generado.');
        });

        return $this->updated(FacturaResource::make($factura->fresh(['lineas', 'impuestos', 'cliente', 'empresa', 'estadoFactura', 'tipoFactura', 'registrosFacturacion']))->resolve(), 'Factura anulada correctamente.');
    }
}
