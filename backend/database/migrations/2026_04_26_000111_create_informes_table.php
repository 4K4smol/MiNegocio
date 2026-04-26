<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('informes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('generado_por')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->string('codigo', 50);
            $table->string('nombre', 140);
            $table->string('tipo', 50);
            $table->string('formato', 20)->default('JSON');
            $table->string('estado_codigo', 30)->default('PENDIENTE');
            $table->json('filtros')->nullable();
            $table->json('resumen')->nullable();
            $table->longText('contenido')->nullable();
            $table->dateTime('generado_en')->nullable();
            $table->timestamps();

            $table->unique(['empresa_id', 'codigo']);
            $table->index(['empresa_id', 'tipo']);
            $table->index(['empresa_id', 'estado_codigo']);
            $table->index(['generado_por', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('informes');
    }
};
