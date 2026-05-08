<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoVerificacion extends Model
{
    protected $table = 'documentos_verificacion';

    protected $fillable = [
        'solicitud_verificacion_id',
        'tipo_documento',
        'archivo',
        'nombre_original',
        'mime_type',
        'tamano',
    ];

    public function solicitudVerificacion(): BelongsTo
    {
        return $this->belongsTo(SolicitudVerificacion::class, 'solicitud_verificacion_id');
    }
}
