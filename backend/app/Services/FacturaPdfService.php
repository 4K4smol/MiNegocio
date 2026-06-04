<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EmpresaFacturacionConfig;
use App\Models\Factura;
use App\Models\FacturaDocumento;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use RuntimeException;

class FacturaPdfService
{
    private const DEFAULT_TEMPLATE = 'base';

    public function __construct(
        private readonly RegistroFacturacionService $registroFacturacionService
    ) {}

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
        $datosQr = $registro
            ? $this->registroFacturacionService->generarPayloadQrInterno($factura, $registro)
            : null;

        $template = $this->templateForFactura($factura);
        $view = 'pdf.facturas.'.$template;

        $pdfContent = Pdf::loadView($view, [
            'factura' => $factura,
            'registro' => $registro,
            'datosQr' => $datosQr,
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
}
