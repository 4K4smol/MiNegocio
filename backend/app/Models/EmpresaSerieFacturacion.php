<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmpresaSerieFacturacion extends Model
{
    protected $table = 'empresa_series_facturacion';

    protected $fillable = ['empresa_id', 'serie', 'nombre', 'descripcion', 'es_default', 'activo'];

    protected $casts = [
        'es_default' => 'boolean',
        'activo' => 'boolean',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
