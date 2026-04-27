<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cliente extends Model
{
    use SoftDeletes;

    protected $table = 'clientes';

    protected $fillable = [
        'tipo_cliente_id',
        'nombre',
        'apellidos',
        'razon_social',
        'dni_cif',
        'telefono',
        'email',
        'persona_contacto',
        'notas',
        'activo',
        'empresa_id'
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function localizaciones(): HasMany
    {
        return $this->hasMany(LocalizacionCliente::class, 'cliente_id');
    }

    public function localizacionPrincipal(): HasOne
    {
        return $this->hasOne(LocalizacionCliente::class, 'cliente_id')
            ->where('es_principal', true);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function tipoCliente(): BelongsTo
    {
        return $this->belongsTo(TipoCliente::class, 'tipo_cliente_id');
    }

    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class, 'cliente_id');
    }

    public function ordenesTrabajo(): HasMany
    {
        return $this->hasMany(OrdenTrabajo::class, 'cliente_id');
    }

        public function calendarioEventos(): HasMany
    {
        return $this->hasMany(CalendarioEvento::class, 'cliente_id');
    }

    public function getNombreCompletoAttribute(): string
    {
        if (($this->tipoCliente?->codigo ?? null) === 'EMPRESA') {
            return $this->razon_social ?: $this->nombre;
        }

        return trim($this->nombre.' '.($this->apellidos ?? ''));
    }
}
