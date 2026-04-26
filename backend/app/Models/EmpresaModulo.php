<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class EmpresaModulo extends Pivot
{
    protected $table = 'empresa_modulos';

    public $incrementing = true;

    protected $fillable = [
        'empresa_id',
        'modulo_id',
        'activo',
        'fecha_activacion',
        'fecha_desactivacion',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'fecha_activacion' => 'datetime',
            'fecha_desactivacion' => 'datetime',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function modulo(): BelongsTo
    {
        return $this->belongsTo(Modulo::class, 'modulo_id');
    }
}
