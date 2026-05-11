<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Models\DocumentoVerificacion;
use App\Services\Admin\SolicitudVerificacionAdminService;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class DocumentoVerificacionController extends ApiController
{
    public function __construct(private readonly SolicitudVerificacionAdminService $service) {}

    public function preview(DocumentoVerificacion $documento): Response|JsonResponse
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('verificaciones');

        if (! $disk->exists($documento->archivo)) {
            return $this->notFound('El documento no existe en el almacenamiento privado.');
        }

        $mimeType = $disk->mimeType($documento->archivo)
            ?: $documento->mime_type
            ?: 'application/octet-stream';

        if (! $this->esPrevisualizable($mimeType)) {
            return $this->error('Este tipo de documento no se puede previsualizar.', 415);
        }

        $this->service->registrarVisualizacionDocumento(
            request()->user(),
            $documento->load('solicitudVerificacion')
        );

        $filename = str_replace(
            ['"', '\\'],
            '',
            $documento->nombre_original ?: basename($documento->archivo)
        );

        return response($disk->get($documento->archivo), 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function esPrevisualizable(string $mimeType): bool
    {
        return $mimeType === 'application/pdf' || str_starts_with($mimeType, 'image/');
    }
}
