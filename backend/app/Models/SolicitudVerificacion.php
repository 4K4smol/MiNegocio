<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SolicitudVerificacion extends Model
{
    protected $table = 'solicitudes_verificacion';

    protected $fillable = [
        'user_id',
        'empresa_id',
        'estado_verificacion_id',
        'revisado_por',
        'observaciones',
        'fecha_revision'
    ];

    protected function casts(): array
    {
        return ['fecha_revision' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
    public function estadoVerificacion(): BelongsTo
    {
        return $this->belongsTo(EstadoVerificacion::class, 'estado_verificacion_id');
    }
    public function revisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisado_por');
    }
    public function documentos(): HasMany
    {
        return $this->hasMany(DocumentoVerificacion::class, 'solicitud_verificacion_id');
    }
}
