<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

class RegistroEventoFacturacion extends Model
{
    protected $table = 'registros_evento_facturacion';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'factura_id',
        'registro_facturacion_id',
        'codigo_evento',
        'descripcion',
        'payload_json',
        'ip',
        'user_agent',
        'generado_at',
    ];

    protected $casts = [
        'payload_json' => 'array',
        'generado_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::deleting(fn() => throw new RuntimeException('No se pueden borrar registros_evento_facturacion.'));
    }
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'factura_id');
    }
    public function registroFacturacion(): BelongsTo
    {
        return $this->belongsTo(RegistroFacturacion::class, 'registro_facturacion_id');
    }
}
