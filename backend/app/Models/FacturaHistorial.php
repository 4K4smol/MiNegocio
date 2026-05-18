<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacturaHistorial extends Model
{
    protected $table = 'factura_historial';

    public const UPDATED_AT = null;

    protected $fillable = [
        'factura_id',
        'empresa_id',
        'user_id',
        'accion',
        'estado_anterior_id',
        'estado_nuevo_id',
        'descripcion',
        'metadatos',
    ];

    protected $casts = [
        'metadatos' => 'array',
    ];

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'factura_id');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
