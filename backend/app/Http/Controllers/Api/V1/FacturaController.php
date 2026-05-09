<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\FacturaResource;
use App\Models\EstadoFactura;
use App\Models\Factura;
use App\Services\RegistroEventoFacturacionService;
use App\Services\RegistroFacturacionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class FacturaController extends AbstractCrudController
{
    public function __construct(private readonly RegistroFacturacionService $registroFacturacionService, private readonly RegistroEventoFacturacionService $registroEventoFacturacionService) {}

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

        if ($factura->estadoFactura?->codigo === 'anulada') {
            throw new RuntimeException('La factura ya está anulada.');
        }

        DB::transaction(function () use ($factura, $estadoAnulada, $request): void {
            $factura->estado_factura_id = $estadoAnulada->id;
            $factura->observaciones = trim((string) $request->input('motivo_anulacion', '')) ?: $factura->observaciones;
            $factura->save();

            $this->registroEventoFacturacionService->registrar([
                'empresa_id' => $factura->empresa_id,
                'user_id' => $request->user()?->id,
                'factura_id' => $factura->id,
                'codigo_evento' => 'FACTURA_ANULADA',
                'descripcion' => 'Factura anulada.',
            ]);

            $registroAnulacion = $this->registroFacturacionService->crearRegistroFacturacionAnulacion($factura, (string) $request->input('motivo_anulacion', 'Anulación de factura'));

            $this->registroEventoFacturacionService->registrar([
                'empresa_id' => $factura->empresa_id,
                'user_id' => $request->user()?->id,
                'factura_id' => $factura->id,
                'registro_facturacion_id' => $registroAnulacion->id,
                'codigo_evento' => 'REGISTRO_FACTURACION_ANULACION_CREADO',
                'descripcion' => 'Registro de anulación generado.',
            ]);
        });

        return $this->updated(FacturaResource::make($factura->fresh(['lineas', 'impuestos', 'cliente', 'empresa', 'estadoFactura', 'tipoFactura', 'registrosFacturacion']))->resolve(), 'Factura anulada correctamente.');
    }
}
