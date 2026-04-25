<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')
                ->constrained('empresas')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('tipo_cliente_id')
                ->constrained('tipos_cliente')
                ->cascadeOnUpdate();
            $table->string('nombre');
            $table->string('apellidos')->nullable();
            $table->string('razon_social')->nullable();

            $table->string('dni_cif', 20);
            $table->string('telefono', 30)->nullable();
            $table->string('email')->nullable();

            $table->string('persona_contacto')->nullable();
            $table->text('notas')->nullable();

            $table->boolean('activo')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'dni_cif']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
