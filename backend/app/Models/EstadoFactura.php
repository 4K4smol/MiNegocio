<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstadoFactura extends Model
{
    protected $table = 'estados_factura';
    protected $fillable = ['codigo', 'nombre', 'descripcion', 'es_final', 'permite_edicion', 'activo', 'orden'];
    protected $casts = ['es_final' => 'boolean', 'permite_edicion' => 'boolean', 'activo' => 'boolean'];

    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class, 'estado_factura_id');
    }
}
