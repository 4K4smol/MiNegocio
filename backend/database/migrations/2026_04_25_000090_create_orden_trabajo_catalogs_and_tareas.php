<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orden_trabajo_estados', function (Blueprint $table): void {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->string('nombre', 80);
            $table->string('descripcion', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->unsignedSmallInteger('orden')->default(1);
            $table->timestamps();

            $table->index(['activo', 'orden']);
        });

        Schema::create('orden_trabajo_prioridades', function (Blueprint $table): void {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->string('nombre', 80);
            $table->string('descripcion', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->unsignedSmallInteger('orden')->default(1);
            $table->timestamps();

            $table->index(['activo', 'orden']);
        });

        Schema::create('tareas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('orden_trabajo_id')
                ->constrained('ordenes_trabajo')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('orden_trabajo_linea_id')
                ->nullable()
                ->constrained('ordenes_trabajo_lineas')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('responsable_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->string('titulo', 140);
            $table->text('descripcion')->nullable();
            $table->string('estado_codigo', 30)->default('PENDIENTE');
            $table->dateTime('fecha_vencimiento')->nullable();
            $table->dateTime('fecha_completada')->nullable();
            $table->unsignedSmallInteger('orden')->default(1);
            $table->boolean('activa')->default(true);
            $table->timestamps();

            $table->index(['orden_trabajo_id', 'estado_codigo']);
            $table->index(['responsable_id', 'estado_codigo']);
            $table->index(['orden_trabajo_id', 'activa']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tareas');
        Schema::dropIfExists('orden_trabajo_prioridades');
        Schema::dropIfExists('orden_trabajo_estados');
    }
};
