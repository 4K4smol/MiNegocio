<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacturaCobro extends Model
{
    protected $table = 'factura_cobros';

    protected $fillable = [
        'factura_id',
        'empresa_id',
        'fecha_cobro',
        'importe',
        'metodo_pago',
        'referencia',
        'observaciones',
        'created_by',
    ];

    protected $casts = [
        'fecha_cobro' => 'date',
        'importe' => 'decimal:2',
    ];

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'factura_id');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
