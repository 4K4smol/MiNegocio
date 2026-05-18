<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmpresaFacturacionConfig extends Model
{
    protected $table = 'empresa_facturacion_config';

    protected $fillable = ['empresa_id', 'serie_default', 'modo_facturacion', 'emitir_desde_borrador', 'metadatos'];

    protected $casts = [
        'emitir_desde_borrador' => 'boolean',
        'metadatos' => 'array',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
