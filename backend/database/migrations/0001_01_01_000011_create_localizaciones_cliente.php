<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('localizaciones_cliente', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cliente_id')
                ->constrained('clientes')
                ->cascadeOnDelete();

            $table->string('nombre_localizacion');
            $table->enum('tipo_localizacion', [
                'principal',
                'oficina',
                'local',
                'almacen',
                'centro_trabajo',
                'otro'
            ])->default('otro');

            $table->string('direccion');
            $table->string('direccion_linea_2')->nullable();
            $table->string('ciudad');
            $table->string('provincia')->nullable();
            $table->string('codigo_postal', 15)->nullable();
            $table->string('pais')->default('España');

            $table->text('observaciones')->nullable();

            $table->boolean('es_principal')->default(false);
            $table->boolean('activo')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('localizaciones_cliente');
    }
};
