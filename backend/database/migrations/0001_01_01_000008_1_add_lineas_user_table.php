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

            $table->foreignId('rol_id')
                ->nullable()
                ->after('empresa_id')
                ->constrained('roles')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->string('nombre', 100)->nullable()->after('rol_id');
            $table->string('apellido1', 100)->nullable()->after('nombre');
            $table->string('apellido2', 100)->nullable()->after('apellido1');
            $table->string('telefono', 30)->nullable()->after('email');
            $table->boolean('activo')->default(true)->after('telefono');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('empresa_id');
            $table->dropConstrainedForeignId('rol_id');
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
