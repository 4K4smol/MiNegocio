<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Factura;
use App\Models\ModoRemisionFacturacion;
use App\Models\RegistroEvento;
use App\Models\RegistroFacturacion;
use App\Models\TipoEventoFacturacion;
use App\Models\TipoRegistroFacturacion;
use Illuminate\Support\Carbon;
use RuntimeException;

class RegistroFacturacionService
{
    private const CODIGO_SISTEMA = 'MINNEGOCIO-RRSIF';

    public function generarRegistroAlta(Factura $factura): RegistroFacturacion
    {
        return $this->crearRegistro($factura, 'alta');
    }

    public function generarRegistroAnulacion(Factura $factura, string $motivo): RegistroFacturacion
    {
        return $this->crearRegistro($factura, 'anulacion', $motivo);
    }

    public function calcularHash(array $payload): string
    {
        return hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    public function obtenerRegistroAnterior(int $empresaId): ?RegistroFacturacion
    {
        return RegistroFacturacion::query()->where('empresa_id', $empresaId)->orderByDesc('id')->lockForUpdate()->first();
    }

    public function construirXmlAlta(Factura $factura, ?RegistroFacturacion $anterior, string $hash, Carbon $generadoAt): string
    {
        return $this->buildXml($factura, 'alta', $anterior, $hash, $generadoAt, null);
    }

    public function construirXmlAnulacion(Factura $factura, ?RegistroFacturacion $anterior, string $hash, Carbon $generadoAt, string $motivo): string
    {
        return $this->buildXml($factura, 'anulacion', $anterior, $hash, $generadoAt, $motivo);
    }

    public function registrarEvento(int $empresaId, string $codigoEvento, string $descripcion): RegistroEvento
    {
        $modo = ModoRemisionFacturacion::query()->where('codigo', 'verifactu')->first();
        if (! $modo) throw new RuntimeException('Falta modo de remisión verifactu.');

        $tipo = TipoEventoFacturacion::query()->where('codigo', strtolower($codigoEvento))->first()
            ?? TipoEventoFacturacion::query()->firstOrCreate(['codigo' => strtolower($codigoEvento)], ['nombre' => $codigoEvento, 'activo' => true, 'orden' => 999]);

        $anterior = RegistroEvento::query()->where('empresa_id', $empresaId)->orderByDesc('id')->lockForUpdate()->first();
        $generadoAt = now();
        $payload = ['empresa_id' => $empresaId, 'codigo_evento' => $codigoEvento, 'descripcion' => $descripcion, 'anterior' => $anterior?->hash_actual, 'generado_at' => $generadoAt->toISOString()];
        $hash = $this->calcularHash($payload);

        return RegistroEvento::query()->create([
            'empresa_id' => $empresaId,
            'tipo_evento_facturacion_id' => $tipo->id,
            'modo_remision_facturacion_id' => $modo->id,
            'codigo_evento' => $codigoEvento,
            'descripcion' => $descripcion,
            'generado_at' => $generadoAt,
            'hash_actual' => $hash,
            'hash_anterior_64' => $anterior?->hash_actual,
            'xml_contenido' => '<Evento codigo="'.$this->xml($codigoEvento).'"><Descripcion>'.$this->xml($descripcion).'</Descripcion></Evento>',
            'xml_version' => '1.0',
        ]);
    }

    private function crearRegistro(Factura $factura, string $tipoCodigo, ?string $motivo = null): RegistroFacturacion
    {
        $tipo = TipoRegistroFacturacion::query()->where('codigo', $tipoCodigo)->first();
        $modo = ModoRemisionFacturacion::query()->where('codigo', 'verifactu')->first();
        if (! $tipo || ! $modo) throw new RuntimeException('Faltan catálogos de registro de facturación.');

        $anterior = $this->obtenerRegistroAnterior((int) $factura->empresa_id);
        $generadoAt = now();
        $fechaExp = $factura->fecha_emision->toDateString();

        $payload = ['tipo' => $tipoCodigo, 'factura_id' => $factura->id, 'serie' => $factura->serie, 'numero' => $factura->numero, 'fecha_expedicion' => $fechaExp, 'importe_total' => $factura->total, 'registro_anterior_hash_64' => $anterior?->hash_actual, 'motivo' => $motivo, 'generado_at' => $generadoAt->toISOString()];
        $hash = $this->calcularHash($payload);

        return RegistroFacturacion::query()->create([
            'factura_id' => $factura->id,
            'tipo_registro_facturacion_id' => $tipo->id,
            'modo_remision_facturacion_id' => $modo->id,
            'empresa_id' => $factura->empresa_id,
            'emisor_nif' => $factura->emisor_nif,
            'emisor_nombre_razon_social' => $factura->emisor_nombre_razon_social,
            'serie' => $factura->serie,
            'numero' => $factura->numero,
            'fecha_expedicion' => $fechaExp,
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
            'xml_contenido' => $tipoCodigo === 'alta' ? $this->construirXmlAlta($factura, $anterior, $hash, $generadoAt) : $this->construirXmlAnulacion($factura, $anterior, $hash, $generadoAt, (string) $motivo),
            'xml_version' => '1.0',
            'codigo_sistema_informatico' => self::CODIGO_SISTEMA,
            'nombre_sistema' => (string) config('app.name', 'MiNegocio'),
            'version_sistema' => (string) config('app.version', '1.0.0'),
            'numero_instalacion' => 'EMP-'.$factura->empresa_id,
            'tipo_uso_posible_solo_verifactu' => true,
            'tipo_uso_posible_multi_ot' => true,
            'indicador_multiples_ot' => false,
            'productor_nif' => $factura->emisor_nif,
            'productor_nombre' => (string) config('app.name', 'MiNegocio'),
        ]);
    }

    private function buildXml(Factura $factura, string $tipo, ?RegistroFacturacion $anterior, string $hash, Carbon $generadoAt, ?string $motivo): string
    {
        return '<RegistroFacturacion tipo="'.$tipo.'"><Factura><Serie>'.$this->xml($factura->serie).'</Serie><Numero>'.$this->xml($factura->numero).'</Numero></Factura><Hash>'.$this->xml($hash).'</Hash><Motivo>'.$this->xml($motivo).'</Motivo><Anterior>'.$this->xml($anterior?->hash_actual).'</Anterior><GeneradoAt>'.$this->xml($generadoAt->toISOString()).'</GeneradoAt></RegistroFacturacion>';
    }

    private function xml(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
