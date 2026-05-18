<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecuenciaFacturacion extends Model
{
    protected $table = 'secuencias_facturacion';

    protected $fillable = ['empresa_id', 'ejercicio', 'serie', 'ultimo_numero'];

    protected $casts = [
        'empresa_id' => 'integer',
        'ejercicio' => 'integer',
        'ultimo_numero' => 'integer',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
