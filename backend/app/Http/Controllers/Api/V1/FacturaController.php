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
use App\Services\FacturaRectificativaService;
use App\Services\FacturaService;
use App\Services\RegistroEventoFacturacionService;
use App\Services\RegistroFacturacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

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

        if (! $item) {
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
        if (! $factura instanceof Factura) {
            return $this->notFound();
        }

        $factura = $this->facturaService->actualizarBorrador($factura, $request->validated(), $request->user());

        return $this->updated(FacturaResource::make($factura)->resolve(), 'Factura actualizada.');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $factura = $this->findRecord($request, $id);
        if (! $factura instanceof Factura) {
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
        if (! $this->findRecord($request, $factura->id)) {
            return $this->forbidden();
        }

        $factura = $this->facturaService->emitir($factura, $request->user(), $request->validated());

        return $this->updated(FacturaResource::make($factura)->resolve(), 'Factura emitida correctamente.');
    }

    public function marcarPagada(Request $request, Factura $factura): JsonResponse
    {
        if (! $this->findRecord($request, $factura->id)) {
            return $this->forbidden();
        }

        $pendiente = max(0, round((float) $factura->total - (float) $factura->cobros()->sum('importe'), 2));

        $factura = $this->facturaCobroService->registrarCobro($factura, [
            'importe' => $pendiente > 0 ? $pendiente : (float) $factura->total,
            'fecha_cobro' => now()->toDateString(),
            'metodo_pago' => $request->input('metodo_pago', 'manual'),
            'observaciones' => $request->input('observaciones_pago', 'Marcada como pagada manualmente.'),
        ], $request->user());

        return $this->updated(FacturaResource::make($factura)->resolve(), 'Factura marcada como pagada.');
    }

    public function registrarCobro(StoreFacturaCobroRequest $request, Factura $factura): JsonResponse
    {
        if (! $this->findRecord($request, $factura->id)) {
            return $this->forbidden();
        }

        $factura = $this->facturaCobroService->registrarCobro($factura, $request->validated(), $request->user());

        return $this->created(FacturaResource::make($factura)->resolve(), 'Cobro registrado correctamente.');
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
            throw new RuntimeException('La factura ya esta anulada.');
        }

        DB::transaction(function () use ($factura, $estadoAnulada, $request): void {
            $estadoAnteriorId = $factura->estado_factura_id;
            $factura->estado_factura_id = $estadoAnulada->id;
            $factura->observaciones = trim((string) $request->input('motivo_anulacion', '')) ?: $factura->observaciones;
            $factura->save();

            $this->historialService->registrar($factura, 'factura_anulada', $request->user(), $estadoAnteriorId, $estadoAnulada->id, 'Factura anulada.', [
                'motivo' => $request->input('motivo_anulacion'),
            ]);

            $this->registroEventoFacturacionService->registrar([
                'empresa_id' => $factura->empresa_id,
                'user_id' => $request->user()?->id,
                'factura_id' => $factura->id,
                'codigo_evento' => 'FACTURA_ANULADA',
                'descripcion' => 'Factura anulada.',
            ]);

            $registroAnulacion = $this->registroFacturacionService->crearRegistroFacturacionAnulacion($factura, (string) $request->input('motivo_anulacion', 'Anulacion de factura'));

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
        if (! $this->findRecord($request, $factura->id)) {
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
        if (! $this->findRecord($request, $factura->id)) {
            return $this->forbidden();
        }

        return $this->success(
            FacturaHistorialResource::collection($factura->historial()->orderBy('id')->get())->resolve(),
            'Historial de factura.'
        );
    }

    public function registrosFacturacion(Request $request, Factura $factura): JsonResponse
    {
        if (! $this->findRecord($request, $factura->id)) {
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
