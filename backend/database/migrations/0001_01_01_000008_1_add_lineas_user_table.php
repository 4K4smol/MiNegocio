<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('empresa_id')
                ->nullable()
                ->after('id')
                ->constrained('empresas')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('role_id')
                ->nullable()
                ->after('empresa_id')
                ->constrained('roles')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->string('nombre', 100)->nullable()->after('role_id');
            $table->string('apellido1', 100)->nullable()->after('nombre');
            $table->string('apellido2', 100)->nullable()->after('apellido1');
            $table->string('telefono', 30)->nullable()->after('email');
            $table->boolean('activo')->default(true)->after('telefono');

            $table->index(['empresa_id', 'activo']);
            $table->index(['role_id', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('empresa_id');
            $table->dropConstrainedForeignId('role_id');
            $table->dropIndex('users_empresa_id_activo_index');
            $table->dropIndex('users_role_id_activo_index');
            $table->dropColumn([
                'nombre',
                'apellido1',
                'apellido2',
                'telefono',
                'activo',
            ]);
        });
    }
};
