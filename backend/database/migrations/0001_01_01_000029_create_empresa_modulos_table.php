<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresa_modulos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')
                ->constrained('empresas')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('modulo_id')
                ->constrained('modulos')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->boolean('activo')->default(true);
            $table->timestamp('fecha_activacion')->nullable();
            $table->timestamp('fecha_desactivacion')->nullable();
            $table->timestamps();

            $table->unique(['empresa_id', 'modulo_id']);
            $table->index(['empresa_id', 'activo']);
            $table->index(['modulo_id', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresa_modulos');
    }
};
