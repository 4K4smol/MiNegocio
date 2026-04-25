<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventarioMovimiento extends Model
{
    protected $table = 'inventario_movimientos';

    protected $fillable = [
        'empresa_id',
        'inventario_item_id',
        'ubicacion_origen_id',
        'ubicacion_destino_id',
        'tipo_movimiento_id',
        'cantidad',
        'stock_anterior',
        'stock_posterior',
        'motivo',
        'fecha_movimiento',
        'user_id',
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
        'stock_anterior' => 'decimal:2',
        'stock_posterior' => 'decimal:2',
        'fecha_movimiento' => 'datetime',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventarioItem::class, 'inventario_item_id');
    }

    public function ubicacionOrigen(): BelongsTo
    {
        return $this->belongsTo(InventarioUbicacion::class, 'ubicacion_origen_id');
    }

    public function ubicacionDestino(): BelongsTo
    {
        return $this->belongsTo(InventarioUbicacion::class, 'ubicacion_destino_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tipoMovimiento(): BelongsTo
    {
        return $this->belongsTo(TipoInventarioMovimiento::class, 'tipo_movimiento_id');
    }
}
