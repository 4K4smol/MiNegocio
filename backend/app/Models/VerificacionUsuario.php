<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificacionUsuario extends Model
{
    protected $table = 'verificaciones_usuario';

    protected $fillable = [
        'user_id',
        'estado_verificacion_id',
        'tipo_documento_identidad_id',
        'numero_documento',
        'ruta_documento_anverso',
        'ruta_documento_reverso',
        'ruta_selfie',
        'fecha_verificacion',
        'notas_revision',
        'revisado_por',
    ];

    protected $casts = [
        'fecha_verificacion' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function estadoVerificacion(): BelongsTo
    {
        return $this->belongsTo(EstadoVerificacion::class, 'estado_verificacion_id');
    }

    public function tipoDocumentoIdentidad(): BelongsTo
    {
        return $this->belongsTo(TipoDocumentoIdentidad::class, 'tipo_documento_identidad_id');
    }

    public function revisadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisado_por');
    }
}
