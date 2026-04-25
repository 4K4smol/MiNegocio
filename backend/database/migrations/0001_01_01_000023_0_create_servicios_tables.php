<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicios', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('tipo_negocio', 50)->nullable();
            $table->string('codigo', 50)->nullable();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->string('unidad_servicio', 30)->default('ud');
            $table->integer('duracion_estimada_min')->nullable();
            $table->boolean('activo')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'codigo']);
            $table->index(['empresa_id', 'activo']);
            $table->index(['empresa_id', 'tipo_negocio']);
        });

        Schema::create('servicio_tarifas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('codigo', 30);
            $table->string('nombre', 100);
            $table->string('descripcion', 255)->nullable();
            $table->boolean('es_default')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['empresa_id', 'nombre']);
            $table->unique(['empresa_id', 'codigo']);
            $table->index(['empresa_id', 'activo']);
        });

        Schema::create('servicio_precios', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('servicio_id')->constrained('servicios')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('servicio_tarifa_id')->constrained('servicio_tarifas')->restrictOnDelete()->cascadeOnUpdate();
            $table->decimal('precio_base', 12, 2);
            $table->decimal('iva_porcentaje', 5, 2)->default(21.00);
            $table->decimal('retencion_porcentaje', 5, 2)->nullable();
            $table->char('moneda', 3)->default('EUR');
            $table->dateTime('vigente_desde');
            $table->dateTime('vigente_hasta')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['servicio_id', 'servicio_tarifa_id', 'vigente_desde']);
            $table->index(['servicio_id', 'vigente_hasta']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicio_precios');
        Schema::dropIfExists('servicio_tarifas');
        Schema::dropIfExists('servicios');
    }
};
