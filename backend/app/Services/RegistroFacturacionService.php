<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Factura;
use App\Models\ModoRemisionFacturacion;
use App\Models\RegistroFacturacion;
use App\Models\TipoRegistroFacturacion;
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
        return $this->crearRegistro($factura, 'anulacion', $motivo);
    }

    private function crearRegistro(Factura $factura, string $tipoCodigo, ?string $motivo = null): RegistroFacturacion
    {
        $tipo = TipoRegistroFacturacion::query()->where('codigo', $tipoCodigo)->first();
        $modo = ModoRemisionFacturacion::query()->where('codigo', 'verifactu')->first();
        if (! $tipo || ! $modo) throw new RuntimeException('Faltan catálogos de registro de facturación.');

        $anterior = $this->obtenerRegistroAnterior((int) $factura->empresa_id);
        $generadoAt = now();

        $hash = $this->calcularHash([

            'tipo' => $tipoCodigo,
            'factura_id' => $factura->id,
            'serie' => $factura->serie,
            'numero' => $factura->numero,
            'fecha_expedicion' => $factura->fecha_emision->toDateString(),
            'importe_total' => $factura->total,
            'registro_anterior_hash_64' => $anterior?->hash_actual,
            'motivo' => $motivo,
            'generado_at' => $generadoAt->toISOString(),
        ]);

        $data = [
            'factura_id' => $factura->id,
            'tipo_registro_facturacion_id' => $tipo->id,
            'modo_remision_facturacion_id' => $modo->id,
            'empresa_id' => $factura->empresa_id,
            'emisor_nif' => $factura->emisor_nif,
            'emisor_nombre_razon_social' => $factura->emisor_nombre_razon_social,
            'serie' => $factura->serie,
            'numero' => $factura->numero,
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
            'generado_at' => $generadoAt,
            'xml_version' => '1.0',
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

    private function calcularHash(array $payload): string
    {
        return hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }
}
