<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'nombre',
        'apellidos',
        'telefono',
        'empresa_id',
        'role_id',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function verificacion(): HasOne
    {
        return $this->hasOne(VerificacionUsuario::class, 'user_id');
    }

    public function ordenesTrabajoResponsable(): HasMany
    {
        return $this->hasMany(OrdenTrabajo::class, 'tecnico_responsable_id');
    }

    public function partesTrabajo(): HasMany
    {
        return $this->hasMany(OrdenTrabajoParte::class, 'user_id');
    }

    public function tareasResponsable(): HasMany
    {
        return $this->hasMany(Tarea::class, 'responsable_id');
    }
}
