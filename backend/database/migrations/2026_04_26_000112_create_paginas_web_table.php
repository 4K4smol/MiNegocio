<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paginas_web', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('slug', 140);
            $table->string('titulo', 160);
            $table->string('plantilla', 60)->default('base');
            $table->string('estado_codigo', 30)->default('BORRADOR');
            $table->boolean('publicada')->default(false);
            $table->dateTime('publicada_en')->nullable();
            $table->text('resumen')->nullable();
            $table->longText('contenido')->nullable();
            $table->json('configuracion')->nullable();
            $table->timestamps();

            $table->unique(['empresa_id', 'slug']);
            $table->index(['empresa_id', 'publicada']);
            $table->index(['empresa_id', 'estado_codigo']);
            $table->index(['empresa_id', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paginas_web');
    }
};
