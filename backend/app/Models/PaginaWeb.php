<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaginaWeb extends Model
{
    protected $table = 'paginas_web';

    protected $fillable = [
        'empresa_id',
        'slug',
        'titulo',
        'plantilla',
        'estado_codigo',
        'publicada',
        'publicada_en',
        'resumen',
        'contenido',
        'configuracion',
    ];

    protected $casts = [
        'publicada' => 'boolean',
        'publicada_en' => 'datetime',
        'configuracion' => 'array',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function contactos(): HasMany
    {
        return $this->hasMany(PaginaWebContacto::class, 'pagina_web_id');
    }
}
