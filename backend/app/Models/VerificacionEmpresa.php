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
        'observaciones',
        'fecha_verificacion',
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
}
