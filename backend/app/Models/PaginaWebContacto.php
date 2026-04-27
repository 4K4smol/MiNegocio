<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaginaWebContacto extends Model
{
    protected $table = 'paginas_web_contactos';

    protected $fillable = [
        'empresa_id',
        'pagina_web_id',
        'nombre',
        'email',
        'telefono',
        'asunto',
        'mensaje',
        'tratado',
        'tratado_en',
        'meta',
    ];

    protected $casts = [
        'tratado' => 'boolean',
        'tratado_en' => 'datetime',
        'meta' => 'array',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function paginaWeb(): BelongsTo
    {
        return $this->belongsTo(PaginaWeb::class, 'pagina_web_id');
    }
}
