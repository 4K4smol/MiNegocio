<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('role_id')
                ->nullable()
                ->after('empresa_id')
                ->constrained('roles')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->string('apellidos', 200)
                ->nullable()
                ->after('name');

            $table->index(['empresa_id', 'activo']);
            $table->index(['role_id', 'activo']);
        });

        DB::table('users')
            ->whereNull('role_id')
            ->whereNotNull('rol_id')
            ->update(['role_id' => DB::raw('rol_id')]);

        DB::statement("UPDATE users SET apellidos = TRIM(CONCAT(COALESCE(apellido1, ''), ' ', COALESCE(apellido2, ''))) WHERE apellidos IS NULL");

        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('rol_id');
        });

        Schema::table('ordenes_trabajo', function (Blueprint $table): void {
            $table->foreignId('estado_id')
                ->nullable()
                ->after('numero')
                ->constrained('orden_trabajo_estados')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('prioridad_id')
                ->nullable()
                ->after('canal_origen')
                ->constrained('orden_trabajo_prioridades')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->index(['empresa_id', 'estado_id']);
            $table->index(['empresa_id', 'prioridad_id']);
        });
    }

    public function down(): void
    {
        Schema::table('ordenes_trabajo', function (Blueprint $table): void {
            $table->dropForeign(['estado_id']);
            $table->dropForeign(['prioridad_id']);
            $table->dropIndex('ordenes_trabajo_empresa_id_estado_id_index');
            $table->dropIndex('ordenes_trabajo_empresa_id_prioridad_id_index');
            $table->dropColumn(['estado_id', 'prioridad_id']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('rol_id')
                ->nullable()
                ->after('empresa_id')
                ->constrained('roles')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });

        DB::table('users')
            ->whereNull('rol_id')
            ->whereNotNull('role_id')
            ->update(['rol_id' => DB::raw('role_id')]);

        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['role_id']);
            $table->dropIndex('users_empresa_id_activo_index');
            $table->dropIndex('users_role_id_activo_index');
            $table->dropColumn(['role_id', 'apellidos']);
        });
    }
};
