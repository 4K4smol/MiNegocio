<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EmpresaFacturacionConfig;
use App\Models\Factura;
use App\Models\FacturaDocumento;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use RuntimeException;

class FacturaPdfService
{
    private const DEFAULT_TEMPLATE = 'base';

    public function generar(Factura $factura, ?User $user = null): FacturaDocumento
    {
        $factura->loadMissing([
            'empresa',
            'cliente',
            'lineas',
            'impuestos',
            'estadoFactura',
            'tipoFactura',
            'registrosFacturacion.tipoRegistroFacturacion',
            'registrosFacturacion.modoRemisionFacturacion',
            'registrosFacturacion.estadoRemisionFacturacion',
        ]);

        if ($factura->estadoFactura?->codigo === 'borrador') {
            throw new RuntimeException('No se puede descargar el PDF de una factura en borrador.');
        }

        $registro = $factura->registrosFacturacion->sortByDesc('id')->first();
        $qrUrl = $registro ? $this->generarQrUrl($factura) : null;
        $qrImageDataUri = $qrUrl ? $this->generarQrImageDataUri($qrUrl) : null;
        $remisionReal = $this->remisionReal();

        $template = $this->templateForFactura($factura);
        $view = 'pdf.facturas.'.$template;

        $pdfContent = Pdf::loadView($view, [
            'factura' => $factura,
            'registro' => $registro,
            'qrImageDataUri' => $qrImageDataUri,
            'qrLegalText' => $remisionReal
                ? 'Factura verificable en la sede electrónica de la AEAT'
                : 'Entorno de pruebas - no válido fiscalmente',
            'qrUrl' => $qrUrl,
        ])
            ->setPaper('a4')
            ->output();

        $ruta = sprintf('facturas/%d/%d.pdf', (int) $factura->empresa_id, (int) $factura->id);
        Storage::disk('local')->put($ruta, $pdfContent);

        return FacturaDocumento::query()->updateOrCreate(
            [
                'factura_id' => $factura->id,
                'tipo' => 'pdf',
            ],
            [
                'empresa_id' => $factura->empresa_id,
                'ruta' => $ruta,
                'nombre_original' => $this->filename($factura),
                'mime_type' => 'application/pdf',
                'tamano' => strlen($pdfContent),
                'hash_sha256' => hash('sha256', $pdfContent),
                'created_by' => $user?->id,
            ],
        );
    }

    public function absolutePath(FacturaDocumento $documento): string
    {
        return Storage::disk('local')->path($documento->ruta);
    }

    public function filename(Factura $factura): string
    {
        $numero = $factura->numero_completo ?: trim((string) $factura->serie.'-'.(string) $factura->numero, '-');
        $numero = $numero !== '' ? $numero : (string) $factura->id;
        $safeNumero = preg_replace('/[^A-Za-z0-9._-]+/', '-', $numero) ?: (string) $factura->id;

        return 'factura-'.$safeNumero.'.pdf';
    }

    public function generarQrUrl(Factura $factura): string
    {
        $baseUrl = $this->remisionReal()
            ? 'https://www2.agenciatributaria.gob.es/wlpl/TIKE-CONT/ValidarQR'
            : 'https://prewww2.aeat.es/wlpl/TIKE-CONT/ValidarQR';

        $numero = $factura->numero_completo ?: trim((string) $factura->serie.'-'.(string) $factura->numero, '-');

        $params = [
            'nif' => (string) $factura->emisor_nif,
            'numserie' => $numero,
            'fecha' => $factura->fecha_emision?->format('d-m-Y') ?? now()->format('d-m-Y'),
            'importe' => number_format((float) $factura->total, 2, '.', ''),
        ];

        return $baseUrl.'?'.http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    private function templateForFactura(Factura $factura): string
    {
        $config = EmpresaFacturacionConfig::query()
            ->where('empresa_id', $factura->empresa_id)
            ->first();

        $template = (string) ($config?->metadatos['plantilla_pdf'] ?? self::DEFAULT_TEMPLATE);

        if (!preg_match('/^[a-z0-9_-]+$/', $template)) {
            return self::DEFAULT_TEMPLATE;
        }

        return View::exists('pdf.facturas.'.$template)
            ? $template
            : self::DEFAULT_TEMPLATE;
    }

    private function generarQrImageDataUri(string $qrUrl): string
    {
        $builder = new Builder(
            writer: new PngWriter(),
            validateResult: false,
            data: $qrUrl,
            encoding: new Encoding('ISO-8859-1'),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 320,
            margin: 24,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        );

        return $builder->build()->getDataUri();
    }

    private function remisionReal(): bool
    {
        return filter_var(config('services.verifactu.remision_real', false), FILTER_VALIDATE_BOOL);
    }
}
