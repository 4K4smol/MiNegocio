<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Factura;
use App\Models\ModoRemisionFacturacion;
use App\Models\RegistroFacturacion;
use App\Models\EstadoRemisionFacturacion;
use App\Models\TipoRegistroFacturacion;
use Carbon\CarbonInterface;
use RuntimeException;

class RegistroFacturacionService
{
    private const CODIGO_SISTEMA = 'MINNEGOCIO-RRSIF';

    public function __construct(private readonly RegistroFacturacionXmlBuilder $xmlBuilder) {}

    public function obtenerRegistroAnterior(int $empresaId): ?RegistroFacturacion
    {
        return RegistroFacturacion::query()->where('empresa_id', $empresaId)->orderByDesc('id')->lockForUpdate()->first();
    }

    public function crearRegistroFacturacionAlta(Factura $factura): RegistroFacturacion
    {
        return $this->crearRegistro($factura, 'alta');
    }

    public function crearRegistroFacturacionAnulacion(Factura $factura, string $motivo = 'Anulación de factura'): RegistroFacturacion
    {
        $tieneAlta = $factura->registrosFacturacion()
            ->whereHas('tipoRegistroFacturacion', fn ($q) => $q->where('codigo', 'alta'))
            ->exists();

        if (! $tieneAlta) {
            throw new RuntimeException('Solo se pueden anular facturas emitidas con registro de facturacion de alta.');
        }

        return $this->crearRegistro($factura, 'anulacion', $motivo);
    }

    private function crearRegistro(Factura $factura, string $tipoCodigo, ?string $motivo = null): RegistroFacturacion
    {
        $tipo = TipoRegistroFacturacion::query()->where('codigo', $tipoCodigo)->first();
        $modo = ModoRemisionFacturacion::query()->where('codigo', 'verifactu')->first();
        $estadoRemision = EstadoRemisionFacturacion::query()->where('codigo', 'pendiente')->first();
        if (! $tipo || ! $modo) {
            throw new RuntimeException('Faltan catálogos de registro de facturación.');
        }

        $anterior = $this->obtenerRegistroAnterior((int) $factura->empresa_id);
        $registroAnulado = $tipoCodigo === 'anulacion'
            ? $factura->registrosFacturacion()
                ->whereHas('tipoRegistroFacturacion', fn ($query) => $query->where('codigo', 'alta'))
                ->orderByDesc('id')
                ->first()
            : null;
        $generadoAt = now()->setMicrosecond(0);

        $payloadHash = $this->construirPayloadHash($factura, $tipoCodigo, $anterior, $motivo, $generadoAt);
        $hash = $this->calcularHash($payloadHash);

        $data = [
            'factura_id' => $factura->id,
            'tipo_registro_facturacion_id' => $tipo->id,
            'modo_remision_facturacion_id' => $modo->id,
            'estado_remision_facturacion_id' => $estadoRemision?->id,
            'empresa_id' => $factura->empresa_id,
            'registro_anterior_id' => $anterior?->id,
            'registro_anulado_id' => $registroAnulado?->id,
            'emisor_nif' => $factura->emisor_nif,
            'emisor_nombre_razon_social' => $factura->emisor_nombre_razon_social,
            'serie' => $factura->serie,
            'numero' => $factura->numero,
            'numero_completo' => $factura->numero_completo ?: $factura->serie . '-' . $factura->numero,
            'fecha_expedicion' => $factura->fecha_emision->toDateString(),
            'tipo_factura_id' => $factura->tipo_factura_id,
            'cuota_total' => $factura->cuota_iva,
            'importe_total' => $factura->total,
            'primer_registro_cadena' => $anterior === null,
            'registro_anterior_nif' => $anterior?->emisor_nif,
            'registro_anterior_serie' => $anterior?->serie,
            'registro_anterior_numero' => $anterior?->numero,
            'registro_anterior_fecha_expedicion' => $anterior?->fecha_expedicion,
            'registro_anterior_hash_64' => $anterior?->hash_actual,
            'tipo_huella' => 'sha256',
            'hash_actual' => $hash,
            'payload_json' => $payloadHash,
            'generado_at' => $generadoAt,
            'xml_version' => '1.0',
            'qr_contenido' => json_encode($this->generarPayloadQrInterno($factura), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'codigo_sistema_informatico' => self::CODIGO_SISTEMA,
            'nombre_sistema' => (string) config('app.name', 'MiNegocio'),
            'version_sistema' => (string) config('app.version', '1.0.0'),
            'numero_instalacion' => 'EMP-' . $factura->empresa_id,
            'tipo_uso_posible_solo_verifactu' => true,
            'tipo_uso_posible_multi_ot' => true,
            'indicador_multiples_ot' => false,
            'productor_nif' => $factura->emisor_nif,
            'productor_nombre' => (string) config('app.name', 'MiNegocio'),
            'estado_remision' => 'pendiente',
        ];
        $data['xml_contenido'] = $tipoCodigo === 'anulacion'
            ? $this->xmlBuilder->buildAnulacion($factura, $data, $anterior, $motivo)
            : $this->xmlBuilder->buildAlta($factura, $data, $anterior);

        return RegistroFacturacion::query()->create($data);
    }

    public function recalcularHashDesdeRegistro(RegistroFacturacion $registro): string
    {
        $registro->loadMissing(['factura', 'tipoRegistroFacturacion']);

        if (! $registro->factura) {
            throw new RuntimeException('No se puede recalcular el hash sin la factura asociada.');
        }

        return $this->calcularHash($this->construirPayloadHashDesdeRegistro($registro));
    }

    public function construirPayloadHash(
        Factura $factura,
        string $tipoCodigo,
        ?RegistroFacturacion $anterior,
        ?string $motivo,
        CarbonInterface $generadoAt
    ): array {
        $factura->loadMissing('impuestos');

        return [
            'factura_id' => $factura->id,
            'tipo_registro' => $tipoCodigo,
            'emisor_nif' => $factura->emisor_nif,
            'receptor_nif' => $factura->receptor_nif,
            'serie' => $factura->serie,
            'numero' => $factura->numero,
            'fecha_expedicion' => $factura->fecha_emision?->toDateString(),
            'fecha_operacion' => $factura->fecha_operacion?->toDateString(),
            'cuota_total' => $this->normalizarDecimal($factura->cuota_iva),
            'importe_total' => $this->normalizarDecimal($factura->total),
            'desglose_impuestos' => $this->desgloseImpuestos($factura),
            'hash_anterior' => $anterior?->hash_actual,
            'motivo' => $tipoCodigo === 'anulacion' ? $motivo : null,
            'generado_at' => $generadoAt->toISOString(),
        ];
    }

    private function construirPayloadHashDesdeRegistro(RegistroFacturacion $registro): array
    {
        $registro->loadMissing('factura.impuestos');

        return [
            'factura_id' => $registro->factura_id,
            'tipo_registro' => $registro->tipoRegistroFacturacion?->codigo,
            'emisor_nif' => $registro->emisor_nif,
            'receptor_nif' => $registro->factura?->receptor_nif,
            'serie' => $registro->serie,
            'numero' => $registro->numero,
            'fecha_expedicion' => $registro->fecha_expedicion?->toDateString(),
            'fecha_operacion' => $registro->factura?->fecha_operacion?->toDateString(),
            'cuota_total' => $this->normalizarDecimal($registro->cuota_total),
            'importe_total' => $this->normalizarDecimal($registro->importe_total),
            'desglose_impuestos' => $registro->factura ? $this->desgloseImpuestos($registro->factura) : [],
            'hash_anterior' => $registro->registro_anterior_hash_64,
            'motivo' => $registro->tipoRegistroFacturacion?->codigo === 'anulacion'
                ? $this->extraerMotivoDesdeXml($registro)
                : null,
            'generado_at' => $registro->generado_at?->toISOString(),
        ];
    }

    private function extraerMotivoDesdeXml(RegistroFacturacion $registro): ?string
    {
        if (! $registro->xml_contenido) {
            return null;
        }

        $xml = @simplexml_load_string($registro->xml_contenido);

        if (! $xml || ! isset($xml->Motivo)) {
            return null;
        }

        $motivo = trim((string) $xml->Motivo);

        return $motivo !== '' ? $motivo : null;
    }

    public function calcularHash(array $payload): string
    {
        $payload = $this->ordenarPayload($payload);

        return hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    public function generarPayloadQrInterno(Factura $factura, ?RegistroFacturacion $registro = null): array
    {
        $registro ??= $factura->registrosFacturacion()->latest('id')->first();
        $remisionReal = (bool) config('services.verifactu.remision_real', false);

        return [
            'etiqueta' => $remisionReal ? 'VERI*FACTU' : 'Remisión simulada - entorno de pruebas',
            'modo' => $remisionReal ? 'real' : 'simulado',
            'emisor_nif' => $factura->emisor_nif,
            'serie' => $factura->serie,
            'numero' => $factura->numero,
            'fecha_expedicion' => $factura->fecha_emision?->toDateString(),
            'importe_total' => $this->normalizarDecimal($factura->total),
            'hash_registro' => $registro?->hash_actual,
            'url_interna' => url('/verifactu/qr?emisor_nif=' . urlencode((string) $factura->emisor_nif) . '&serie=' . urlencode((string) $factura->serie) . '&numero=' . urlencode((string) $factura->numero)),
        ];
    }

    private function desgloseImpuestos(Factura $factura): array
    {
        return $factura->impuestos
            ->sortBy([
                ['impuesto_codigo', 'asc'],
                ['tipo_porcentaje', 'asc'],
                ['calificacion', 'asc'],
            ])
            ->values()
            ->map(fn($impuesto): array => [
                'impuesto_codigo' => $impuesto->impuesto_codigo,
                'tipo_porcentaje' => $this->normalizarDecimal($impuesto->tipo_porcentaje),
                'base_imponible' => $this->normalizarDecimal($impuesto->base_imponible),
                'cuota' => $this->normalizarDecimal($impuesto->cuota),
                'es_exento' => (bool) $impuesto->es_exento,
                'es_no_sujeto' => (bool) $impuesto->es_no_sujeto,
                'calificacion' => $impuesto->calificacion,
            ])
            ->all();
    }

    private function normalizarDecimal(mixed $valor): string
    {
        return number_format((float) $valor, 2, '.', '');
    }

    private function ordenarPayload(array $payload): array
    {
        ksort($payload);

        foreach ($payload as $clave => $valor) {
            if (is_array($valor)) {
                $payload[$clave] = $this->ordenarPayload($valor);
            }
        }

        return $payload;
    }
}
