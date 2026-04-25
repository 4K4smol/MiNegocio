<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocalizacionCliente extends Model
{
    use SoftDeletes;

    protected $table = 'localizaciones_cliente';

    protected $fillable = [
        'cliente_id',
        'nombre_localizacion',
        'tipo_localizacion_id',
        'direccion',
        'direccion_linea_2',
        'ciudad',
        'provincia',
        'codigo_postal',
        'pais',
        'observaciones',
        'es_principal',
        'activo',
    ];

    protected $casts = [
        'es_principal' => 'boolean',
        'activo' => 'boolean',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function tipoLocalizacion(): BelongsTo
    {
        return $this->belongsTo(TipoLocalizacionCliente::class, 'tipo_localizacion_id');
    }

    public function getDireccionCompletaAttribute(): string
    {
        $partes = array_filter([
            $this->direccion,
            $this->direccion_linea_2,
            $this->codigo_postal,
            $this->ciudad,
            $this->provincia,
            $this->pais,
        ]);

        return implode(', ', $partes);
    }
}
