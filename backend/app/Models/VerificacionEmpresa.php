<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificacionEmpresa extends Model
{
    protected $table = 'verificaciones_empresa';

    protected $fillable = [
        'empresa_id',
        'estado_verificacion_id',
        'ruta_documento_fiscal',
        'ruta_registro_mercantil',
        'ruta_documento_representacion',
        'ruta_poder_apoderamiento',
        'referencia_certificado_digital',
        'fecha_verificacion',
        'notas_revision',
        'revisado_por',
    ];

    protected $casts = [
        'fecha_verificacion' => 'datetime',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function estadoVerificacion(): BelongsTo
    {
        return $this->belongsTo(EstadoVerificacion::class, 'estado_verificacion_id');
    }

    public function revisadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisado_por');
    }
}
