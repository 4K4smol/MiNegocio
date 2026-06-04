<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventarioItem extends Model
{
    use SoftDeletes;

    protected $table = 'inventario_items';

    protected $fillable = [
        'empresa_id',
        'unidad_medida_id',
        'ubicacion_id',
        'nombre',
        'descripcion',
        'stock_actual',
        'stock_minimo',
    ];

    protected $casts = [
        'stock_actual' => 'decimal:2',
        'stock_minimo' => 'decimal:2',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function unidadMedida(): BelongsTo
    {
        return $this->belongsTo(InventarioUnidadMedida::class, 'unidad_medida_id');
    }

    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(InventarioUbicacion::class, 'ubicacion_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(InventarioMovimiento::class, 'inventario_item_id');
    }

    public function existencias(): HasMany
    {
        return $this->hasMany(InventarioExistencia::class, 'inventario_item_id');
    }
}
