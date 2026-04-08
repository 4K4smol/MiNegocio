<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventarioUbicacion extends Model
{
    protected $table = 'inventario_ubicaciones';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'descripcion',
        'tipo',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventarioItem::class, 'ubicacion_id');
    }

    public function movimientosOrigen(): HasMany
    {
        return $this->hasMany(InventarioMovimiento::class, 'ubicacion_origen_id');
    }

    public function movimientosDestino(): HasMany
    {
        return $this->hasMany(InventarioMovimiento::class, 'ubicacion_destino_id');
    }
}
