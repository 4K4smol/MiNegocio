<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\EmitirFacturaRequest;
use App\Http\Requests\Api\V1\StoreFacturaCobroRequest;
use App\Http\Requests\Api\V1\StoreFacturaRequest;
use App\Http\Requests\Api\V1\UpdateFacturaRequest;
use App\Http\Resources\Api\V1\FacturaHistorialResource;
use App\Http\Resources\Api\V1\FacturaResource;
use App\Http\Resources\Api\V1\RegistroFacturacionResource;
use App\Models\EstadoFactura;
use App\Models\Factura;
use App\Services\FacturaCobroService;
use App\Services\FacturaHistorialService;
use App\Services\FacturaPdfService;
use App\Services\FacturaRectificativaService;
use App\Services\FacturaService;
use App\Services\RegistroEventoFacturacionService;
use App\Services\RegistroFacturacionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FacturaController extends AbstractCrudController
{
    public function __construct(
        private readonly RegistroFacturacionService $registroFacturacionService,
        private readonly RegistroEventoFacturacionService $registroEventoFacturacionService,
        private readonly FacturaRectificativaService $facturaRectificativaService,
        private readonly FacturaService $facturaService,
        private readonly FacturaCobroService $facturaCobroService,
        private readonly FacturaHistorialService $historialService
    ) {}

    protected function modelClass(): string
    {
        return Factura::class;
    }

    protected function resourceClass(): ?string
    {
        return FacturaResource::class;
    }

    protected function baseQuery(Request $request): Builder
    {
        return parent::baseQuery($request)
            ->when($request->filled('search'), function (Builder $query) use ($request): void {
                $search = '%'.$request->string('search')->toString().'%';

                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('serie', 'like', $search)
                        ->orWhere('numero', 'like', $search)
                        ->orWhere('numero_completo', 'like', $search)
                        ->orWhere('receptor_nombre_razon_social', 'like', $search)
                        ->orWhere('receptor_nif', 'like', $search)
                        ->orWhereHas('cliente', function (Builder $clienteQuery) use ($search): void {
                            $clienteQuery->where('nombre', 'like', $search)
                                ->orWhere('apellidos', 'like', $search)
                                ->orWhere('razon_social', 'like', $search)
                                ->orWhere('dni_cif', 'like', $search);
                        });
                });
            })
            ->when($request->filled('estado'), function (Builder $query) use ($request): void {
                $estado = $request->string('estado')->toString();

                if ($estado === 'pagada') {
                    $query->where('pagada', true);
                    return;
                }

                $query->whereHas('estadoFactura', fn (Builder $estadoQuery) =>
                    $estadoQuery->where('codigo', $estado)
                );
            });
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->integer('per_page', 15), 1), 100);

        $items = $this->baseQuery($request)
            ->with(['lineas', 'impuestos', 'cliente', 'empresa', 'estadoFactura', 'tipoFactura', 'registrosFacturacion', 'cobros', 'historial'])
            ->paginate($perPage);

        return $this->success(FacturaResource::collection($items)->response()->getData(true));
    }

    public function show(Request $request, int $factura): JsonResponse
    {
        $item = $this->baseQuery($request)
            ->with(['lineas', 'impuestos', 'cliente', 'empresa', 'estadoFactura', 'tipoFactura', 'registrosFacturacion', 'cobros', 'historial'])
            ->whereKey($factura)
            ->first();

        if (!$item) {
            return $this->notFound();
        }

        return $this->success(FacturaResource::make($item)->resolve());
    }

    public function store(StoreFacturaRequest $request): JsonResponse
    {
        $factura = $this->facturaService->crearBorrador($request->validated(), $request->user());

        return $this->created(FacturaResource::make($factura)->resolve(), 'Factura creada en borrador.');
    }

    public function update(UpdateFacturaRequest $request, int $id): JsonResponse
    {
        $factura = $this->findRecord($request, $id);
        if (!$factura instanceof Factura) {
            return $this->notFound();
        }

        $factura = $this->facturaService->actualizarBorrador($factura, $request->validated(), $request->user());

        return $this->updated(FacturaResource::make($factura)->resolve(), 'Factura actualizada.');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $factura = $this->findRecord($request, $id);
        if (!$factura instanceof Factura) {
            return $this->notFound();
        }

        if ($factura->registrosFacturacion()->exists()) {
            throw new RuntimeException('No se puede borrar una factura con registros de facturacion.');
        }

        if ($factura->estadoFactura?->codigo !== 'borrador') {
            throw new RuntimeException('Solo se pueden eliminar borradores sin registros tecnicos.');
        }

        return parent::destroy($request, $id);
    }

    public function emitir(EmitirFacturaRequest $request, Factura $factura): JsonResponse
    {
        if (!$this->findRecord($request, $factura->id)) {
            return $this->forbidden();
        }

        $factura = $this->facturaService->emitir($factura, $request->user(), $request->validated());

        return $this->updated(FacturaResource::make($factura)->resolve(), 'Factura emitida correctamente.');
    }

    public function descargarPdf(Request $request, Factura $factura, FacturaPdfService $facturaPdfService): BinaryFileResponse|JsonResponse
    {
        if (!$this->findRecord($request, $factura->id)) {
            return $this->forbidden();
        }

        $factura->loadMissing(['estadoFactura']);
        if ($factura->estadoFactura?->codigo === 'borrador') {
            return $this->error('No se puede descargar el PDF de una factura en borrador.', 422);
        }

        $documento = $facturaPdfService->generar($factura, $request->user());

        return response()->download(
            $facturaPdfService->absolutePath($documento),
            $documento->nombre_original ?: $facturaPdfService->filename($factura),
            ['Content-Type' => 'application/pdf']
        );
    }

    public function marcarPagada(Request $request, Factura $factura): JsonResponse
    {
        if (!$this->findRecord($request, $factura->id)) {
            return $this->forbidden();
        }

        $total = abs((float) $factura->total);
        $totalCobrado = abs((float) $factura->cobros()->sum('importe'));
        $pendiente = max(0, round($total - $totalCobrado, 2));

        $factura = $this->facturaCobroService->registrarCobro($factura, [
            'importe' => $pendiente > 0 ? $pendiente : $total,
            'fecha_cobro' => now()->toDateString(),
            'metodo_pago' => $request->input('metodo_pago', 'manual'),
            'observaciones' => $request->input('observaciones_pago', 'Marcada como pagada manualmente.'),
        ], $request->user());

        return $this->updated(FacturaResource::make($factura)->resolve(), 'Factura marcada como pagada.');
    }

    public function registrarCobro(StoreFacturaCobroRequest $request, Factura $factura): JsonResponse
    {
        if (!$this->findRecord($request, $factura->id)) {
            return $this->forbidden();
        }

        $factura = $this->facturaCobroService->registrarCobro($factura, $request->validated(), $request->user());

        return $this->created(FacturaResource::make($factura)->resolve(), 'Cobro registrado correctamente.');
    }

    public function anular(Request $request, Factura $factura): JsonResponse
    {
        if (!$this->findRecord($request, $factura->id)) {
            return $this->forbidden();
        }

        $factura->loadMissing(['estadoFactura', 'registrosFacturacion']);

        $estadoAnulada = EstadoFactura::query()->where('codigo', 'anulada')->first();
        if (!$estadoAnulada) {
            throw new RuntimeException('No existe el estado de factura "anulada". Ejecuta los seeders de estados de factura.');
        }

        $codigo_estado = $factura->estadoFactura?->codigo;

        if ($codigo_estado === 'borrador') {
            throw new RuntimeException('No se puede anular una factura en borrador. Elimínala directamente.');
        }

        if ($codigo_estado === 'anulada') {
            throw new RuntimeException('La factura ya esta anulada.');
        }

        if ($codigo_estado === 'rectificada') {
            throw new RuntimeException('No se puede anular una factura que ya ha sido rectificada.');
        }

        if (!in_array($codigo_estado, ['emitida', 'pagada', 'pagada_parcial'], true)) {
            throw new RuntimeException('Solo se pueden anular facturas emitidas o pagadas.');
        }

        $tiene_alta = $factura->registrosFacturacion()
            ->whereHas('tipoRegistroFacturacion', fn ($q) => $q->where('codigo', 'alta'))
            ->exists();

        if (!$tiene_alta) {
            throw new RuntimeException('Solo se pueden anular facturas emitidas con registro de facturacion de alta.');
        }

        DB::transaction(function () use ($factura, $estadoAnulada, $request): void {
            $estadoAnteriorId = $factura->estado_factura_id;
            $motivo = trim((string) $request->input('motivo_anulacion', ''));
            $factura->estado_factura_id = $estadoAnulada->id;
            if ($motivo !== '') {
                $factura->observaciones = $motivo;
            }
            $factura->save();

            $this->historialService->registrar($factura, 'factura_anulada', $request->user(), $estadoAnteriorId, $estadoAnulada->id, 'Factura anulada.', [
                'motivo' => $motivo ?: null,
            ]);

            $this->registroEventoFacturacionService->registrar([
                'empresa_id' => $factura->empresa_id,
                'user_id' => $request->user()?->id,
                'factura_id' => $factura->id,
                'codigo_evento' => 'FACTURA_ANULADA',
                'descripcion' => 'Factura anulada.',
            ]);

            $registroAnulacion = $this->registroFacturacionService->crearRegistroFacturacionAnulacion($factura, $motivo ?: 'Anulacion de factura');

            $this->registroEventoFacturacionService->registrar([
                'empresa_id' => $factura->empresa_id,
                'user_id' => $request->user()?->id,
                'factura_id' => $factura->id,
                'registro_facturacion_id' => $registroAnulacion->id,
                'codigo_evento' => 'REGISTRO_FACTURACION_ANULACION_CREADO',
                'descripcion' => 'Registro de anulacion generado.',
            ]);
        });

        return $this->updated(
            FacturaResource::make($factura->fresh(['lineas', 'impuestos', 'cliente', 'empresa', 'estadoFactura', 'tipoFactura', 'registrosFacturacion', 'historial']))->resolve(),
            'Factura anulada correctamente.'
        );
    }

    public function rectificar(Request $request, Factura $factura): JsonResponse
    {
        if (!$this->findRecord($request, $factura->id)) {
            return $this->forbidden();
        }

        $rectificativa = $this->facturaRectificativaService->generarDesdeFactura(
            $factura,
            $request->user(),
            (string) $request->input('motivo_rectificacion', '')
        );

        return $this->created(FacturaResource::make($rectificativa)->resolve(), 'Factura rectificativa generada correctamente.');
    }

    public function historial(Request $request, Factura $factura): JsonResponse
    {
        if (!$this->findRecord($request, $factura->id)) {
            return $this->forbidden();
        }

        return $this->success(
            FacturaHistorialResource::collection($factura->historial()->orderBy('id')->get())->resolve(),
            'Historial de factura.'
        );
    }

    public function registrosFacturacion(Request $request, Factura $factura): JsonResponse
    {
        if (!$this->findRecord($request, $factura->id)) {
            return $this->forbidden();
        }

        return $this->success(
            RegistroFacturacionResource::collection(
                $factura->registrosFacturacion()
                    ->with(['tipoRegistroFacturacion', 'modoRemisionFacturacion', 'estadoRemisionFacturacion'])
                    ->orderBy('id')
                    ->get()
            )->resolve(),
            'Registros tecnicos de la factura.'
        );
    }
}
