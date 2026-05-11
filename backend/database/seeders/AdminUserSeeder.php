<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::query()->firstOrCreate(
            ['nombre' => 'admin'],
            ['descripcion' => 'Administrador global del sistema'],
        );

        User::query()->updateOrCreate(
            ['email' => 'admin@admin.es'],
            [
                'name' => 'Admin',
                'nombre' => 'Admin',
                'apellido1' => 'Sistema',
                'apellido2' => null,
                'telefono' => null,
                'empresa_id' => null,
                'role_id' => $role->id,
                'activo' => true,
                'password' => Hash::make('admin'),
            ],
        );
    }
}
