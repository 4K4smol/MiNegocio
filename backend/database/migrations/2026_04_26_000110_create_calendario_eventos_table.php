<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendario_eventos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('orden_trabajo_id')->nullable()->constrained('ordenes_trabajo')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->string('titulo', 140);
            $table->text('descripcion')->nullable();
            $table->string('tipo', 30)->default('TAREA');
            $table->dateTime('inicio');
            $table->dateTime('fin')->nullable();
            $table->boolean('todo_el_dia')->default(false);
            $table->string('estado_codigo', 30)->default('PROGRAMADO');
            $table->string('ubicacion', 255)->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['empresa_id', 'inicio']);
            $table->index(['empresa_id', 'estado_codigo']);
            $table->index(['cliente_id', 'inicio']);
            $table->index(['orden_trabajo_id', 'inicio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendario_eventos');
    }
};
