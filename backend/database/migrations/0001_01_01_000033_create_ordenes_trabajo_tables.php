<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordenes_trabajo', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('cliente_id')->constrained('clientes')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('numero', 50);
            $table->string('estado_codigo', 30)->default('BORRADOR');
            $table->string('canal_origen', 30)->nullable();
            $table->string('prioridad_codigo', 30)->nullable();
            $table->date('fecha_apertura');
            $table->dateTime('fecha_programada_inicio')->nullable();
            $table->dateTime('fecha_programada_fin')->nullable();
            $table->dateTime('fecha_inicio_real')->nullable();
            $table->dateTime('fecha_fin_real')->nullable();
            $table->dateTime('fecha_cierre')->nullable();
            $table->foreignId('tecnico_responsable_id')->nullable()->constrained('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->decimal('descuento_global_porcentaje', 5, 2)->default(0.00);
            $table->text('notas_internas')->nullable();
            $table->text('notas_cliente')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['empresa_id', 'numero']);
            $table->index(['empresa_id', 'estado_codigo']);
            $table->index(['cliente_id', 'fecha_apertura']);
            $table->index(['empresa_id', 'fecha_programada_inicio']);
        });

        Schema::create('ordenes_trabajo_lineas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('orden_trabajo_id')->constrained('ordenes_trabajo')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('servicio_id')->nullable()->constrained('servicios')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('estado_codigo', 30)->default('PENDIENTE');
            $table->text('descripcion');
            $table->decimal('cantidad', 10, 2)->default(1.00);
            $table->string('unidad_snapshot', 30);
            $table->decimal('precio_unitario', 12, 2)->default(0.00);
            $table->decimal('descuento_porcentaje', 5, 2)->default(0.00);
            $table->decimal('base_imponible', 12, 2)->default(0.00);
            $table->decimal('iva_porcentaje', 5, 2)->default(21.00);
            $table->decimal('cuota_iva', 12, 2)->default(0.00);
            $table->decimal('total', 12, 2)->default(0.00);
            $table->boolean('facturable')->default(true);
            $table->decimal('facturado_cantidad', 10, 2)->default(0.00);
            $table->integer('orden')->default(1);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['orden_trabajo_id', 'orden']);
            $table->index(['orden_trabajo_id', 'facturable']);
            $table->index(['orden_trabajo_id', 'estado_codigo']);
            $table->index('servicio_id');
        });

        Schema::create('ordenes_trabajo_partes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('orden_trabajo_id')->constrained('ordenes_trabajo')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('orden_trabajo_linea_id')->nullable()->constrained('ordenes_trabajo_lineas')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->dateTime('inicio');
            $table->dateTime('fin')->nullable();
            $table->integer('minutos');
            $table->text('descripcion')->nullable();
            $table->boolean('facturable')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['orden_trabajo_id', 'inicio']);
            $table->index(['user_id', 'inicio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordenes_trabajo_partes');
        Schema::dropIfExists('ordenes_trabajo_lineas');
        Schema::dropIfExists('ordenes_trabajo');
    }
};
