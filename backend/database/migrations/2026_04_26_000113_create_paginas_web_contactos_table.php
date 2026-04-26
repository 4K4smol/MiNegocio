<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paginas_web_contactos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('pagina_web_id')->nullable()->constrained('paginas_web')->nullOnDelete()->cascadeOnUpdate();
            $table->string('nombre', 120);
            $table->string('email', 160)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('asunto', 160)->nullable();
            $table->text('mensaje');
            $table->boolean('tratado')->default(false);
            $table->dateTime('tratado_en')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['empresa_id', 'tratado']);
            $table->index(['empresa_id', 'created_at']);
            $table->index(['pagina_web_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paginas_web_contactos');
    }
};
