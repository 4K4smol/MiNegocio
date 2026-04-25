<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'nombre',
        'apellido1',
        'apellido2',
        'telefono',
        'empresa_id',
        'rol_id',
        'activo'
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
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'rol_id');
    }

    public function verificaciones(): HasMany
    {
        return $this->hasMany(VerificacionUsuario::class, 'user_id');
    }

        public function ordenesTrabajoResponsable(): HasMany
    {
        return $this->hasMany(OrdenTrabajo::class, 'tecnico_responsable_id');
    }

    public function partesTrabajo(): HasMany
    {
        return $this->hasMany(OrdenTrabajoParte::class, 'user_id');
    }
}
