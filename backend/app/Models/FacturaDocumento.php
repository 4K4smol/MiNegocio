<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacturaDocumento extends Model
{
    protected $table = 'factura_documentos';

    protected $fillable = [
        'factura_id',
        'empresa_id',
        'tipo',
        'ruta',
        'nombre_original',
        'mime_type',
        'tamano',
        'hash_sha256',
        'created_by',
    ];

    protected $casts = [
        'tamano' => 'integer',
    ];

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'factura_id');
    }
}
