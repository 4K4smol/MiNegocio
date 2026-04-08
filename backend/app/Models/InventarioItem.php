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
        'categoria_id',
        'unidad_medida_id',
        'ubicacion_id',
        'nombre',
        'descripcion',
        'sku',
        'codigo_barras',
        'stock_actual',
        'stock_minimo',
        'coste_unitario',
        'activo',
        'empresa_id'
    ];

    protected $casts = [
        'stock_actual' => 'decimal:2',
        'stock_minimo' => 'decimal:2',
        'coste_unitario' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(InventarioCategoria::class, 'categoria_id');
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
}
