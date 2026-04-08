<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tipo_empresa_id')
                ->constrained('tipos_empresa')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->string('nombre_fiscal', 150);
            $table->string('nombre_comercial', 150)->nullable();
            $table->string('nif', 20)->unique();
            $table->string('correo', 150)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('direccion_fiscal', 255)->nullable();
            $table->string('codigo_postal', 15)->nullable();
            $table->string('municipio', 100)->nullable();
            $table->string('provincia', 100)->nullable();
            $table->string('pais', 100)->default('España');
            $table->boolean('activa')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
