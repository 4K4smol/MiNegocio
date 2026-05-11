<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminVerificacionEvento extends Model
{
    protected $table = 'admin_verificacion_eventos';

    protected $fillable = [
        'user_admin_id',
        'user_id',
        'empresa_id',
        'solicitud_verificacion_id',
        'accion',
        'estado_anterior',
        'estado_nuevo',
        'motivo',
        'metadatos',
    ];

    protected function casts(): array
    {
        return [
            'metadatos' => 'array',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_admin_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function solicitudVerificacion(): BelongsTo
    {
        return $this->belongsTo(SolicitudVerificacion::class, 'solicitud_verificacion_id');
    }
}
