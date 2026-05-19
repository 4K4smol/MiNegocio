<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\VerifactuRealNoConfiguradoException;
use App\Models\RegistroFacturacion;
use App\Services\VerifactuRemisionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerifactuController extends ApiController
{
    public function __construct(private readonly VerifactuRemisionService $remisionService) {}

    public function estado(Request $request): JsonResponse
    {
        $query = $this->registrosQuery($request);
        $modo = (string) config('services.verifactu.modo', 'simulado');

        return $this->success([
            'estado' => $this->estadoOperativo($modo),
            'modo' => $modo,
            'remision_real_configurada' => $this->remisionRealConfigurada(),
            'ultimo_envio' => (clone $query)
                ->whereNotNull('enviado_aeat_at')
                ->max('enviado_aeat_at'),
            'estadisticas' => [
                'total' => (clone $query)->count(),
                'pendientes' => (clone $query)
                    ->where(fn (Builder $inner) => $inner->where('estado_remision', 'pendiente')->orWhereNull('estado_remision'))
                    ->whereNull('enviado_aeat_at')
                    ->count(),
                'enviados' => (clone $query)
                    ->whereIn('estado_remision', ['enviado', 'enviado_simulado', 'aceptado'])
                    ->count(),
                'aceptados' => (clone $query)
                    ->whereIn('estado_remision', ['aceptado', 'enviado', 'enviado_simulado'])
                    ->count(),
                'rechazados' => (clone $query)
                    ->where('estado_remision', 'rechazado')
                    ->count(),
                'errores' => (clone $query)
                    ->where(fn (Builder $inner) => $inner
                        ->where('estado_remision', 'like', 'error%')
                        ->orWhereNotNull('codigo_error_aeat'))
                    ->count(),
            ],
        ]);
    }

    public function configuracion(Request $request): JsonResponse
    {
        $modo = (string) config('services.verifactu.modo', 'simulado');

        return $this->success([
            'modo' => $modo,
            'activo' => $modo === 'simulado' || $this->remisionRealConfigurada(),
            'remision_real_configurada' => $this->remisionRealConfigurada(),
            'endpoint_configurado' => filled(config('services.verifactu.endpoint')),
            'certificado_configurado' => filled(config('services.verifactu.cert_path')),
            'nif_titular' => $request->user()?->empresa?->nif,
            'nombre_razon_social' => $request->user()?->empresa?->nombre_fiscal,
        ]);
    }

    public function incidencias(Request $request): JsonResponse
    {
        $incidencias = $this->registrosQuery($request)
            ->with(['factura'])
            ->where(fn (Builder $query) => $query
                ->where('estado_remision', 'like', 'error%')
                ->orWhere('estado_remision', 'rechazado')
                ->orWhereNotNull('codigo_error_aeat'))
            ->latest('ultimo_intento_at')
            ->limit(50)
            ->get()
            ->map(fn (RegistroFacturacion $registro): array => [
                'id' => $registro->id,
                'factura_id' => $registro->factura_id,
                'fecha' => $registro->ultimo_intento_at ?? $registro->updated_at,
                'tipo' => $registro->codigo_error_aeat ?: $registro->estado_remision,
                'descripcion' => $registro->descripcion_error_aeat ?: 'Incidencia de remision VeriFactu.',
                'estado' => $registro->estado_remision ?: 'error',
                'serie' => $registro->serie,
                'numero' => $registro->numero,
            ]);

        return $this->success($incidencias);
    }

    public function enviarPendientes(Request $request): JsonResponse
    {
        try {
            return $this->success(
                $this->remisionService->enviarPendientes($request->user()),
                'Envio VeriFactu procesado.'
            );
        } catch (VerifactuRealNoConfiguradoException $exception) {
            return $this->error($exception->getMessage(), 409);
        }
    }

    private function registrosQuery(Request $request): Builder
    {
        $query = RegistroFacturacion::query();
        $user = $request->user();

        if (strtolower((string) $user?->role?->nombre) !== 'admin') {
            return $query->where('empresa_id', $user?->empresa_id);
        }

        if ($request->filled('empresa_id')) {
            $query->where('empresa_id', $request->integer('empresa_id'));
        }

        return $query;
    }

    private function estadoOperativo(string $modo): string
    {
        if ($modo === 'simulado') {
            return 'simulado';
        }

        if ($modo === 'real' && $this->remisionRealConfigurada()) {
            return 'real_configurado';
        }

        return 'no_configurado';
    }

    private function remisionRealConfigurada(): bool
    {
        return filled(config('services.verifactu.endpoint'))
            && filled(config('services.verifactu.cert_path'))
            && filled(config('services.verifactu.cert_password'));
    }
}
