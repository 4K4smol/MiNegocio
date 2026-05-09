<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Factura;
use App\Models\RegistroFacturacion;

class RegistroFacturacionXmlBuilder
{
    public function buildAlta(Factura $factura, RegistroFacturacion|array|null $registro, ?RegistroFacturacion $anterior = null): string
    {
        return $this->build($factura, 'alta', $registro, $anterior, null);
    }

    public function buildAnulacion(Factura $factura, RegistroFacturacion|array|null $registro, ?RegistroFacturacion $anterior = null, ?string $motivo = null): string
    {
        return $this->build($factura, 'anulacion', $registro, $anterior, $motivo);
    }

    private function build(Factura $factura, string $tipo, RegistroFacturacion|array|null $registro, ?RegistroFacturacion $anterior, ?string $motivo): string
    {
        $r = is_array($registro) ? $registro : ($registro?->toArray() ?? []);
        $fechaGeneracion = (string) ($r['generado_at'] ?? now()->toISOString());
        $hashActual = (string) ($r['hash_actual'] ?? '');
        $hashAnterior = (string) ($anterior?->hash_actual ?? $r['registro_anterior_hash_64'] ?? '');

        return '<RegistroFacturacion>'
            .'<TipoRegistro>'.$this->xml($tipo).'</TipoRegistro>'
            .'<Sistema><Codigo>'. $this->xml((string) ($r['codigo_sistema_informatico'] ?? 'MINNEGOCIO-RRSIF')) .'</Codigo><Nombre>'.$this->xml((string) config('app.name', 'MiNegocio')).'</Nombre></Sistema>'
            .'<Emisor><Nif>'.$this->xml($factura->emisor_nif).'</Nif><Nombre>'.$this->xml($factura->emisor_nombre_razon_social).'</Nombre></Emisor>'
            .'<Factura><Serie>'.$this->xml($factura->serie).'</Serie><Numero>'.$this->xml($factura->numero).'</Numero><FechaExpedicion>'.$this->xml($factura->fecha_emision?->toDateString()).'</FechaExpedicion><TipoFacturaId>'.$this->xml((string) $factura->tipo_factura_id).'</TipoFacturaId><CuotaTotal>'.$this->xml((string) $factura->cuota_iva).'</CuotaTotal><ImporteTotal>'.$this->xml((string) $factura->total).'</ImporteTotal></Factura>'
            .'<Huella><Actual>'.$this->xml($hashActual).'</Actual><Anterior>'.$this->xml($hashAnterior).'</Anterior></Huella>'
            .'<RegistroAnterior><Nif>'.$this->xml($anterior?->emisor_nif ?? $r['registro_anterior_nif'] ?? null).'</Nif><Serie>'.$this->xml($anterior?->serie ?? $r['registro_anterior_serie'] ?? null).'</Serie><Numero>'.$this->xml($anterior?->numero ?? $r['registro_anterior_numero'] ?? null).'</Numero></RegistroAnterior>'
            .'<FechaGeneracion>'.$this->xml($fechaGeneracion).'</FechaGeneracion>'
            .'<Motivo>'.$this->xml($motivo).'</Motivo>'
            .'</RegistroFacturacion>';
    }

    private function xml(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
