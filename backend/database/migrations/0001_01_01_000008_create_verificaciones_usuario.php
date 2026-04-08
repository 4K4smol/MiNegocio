<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verificaciones_usuario', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('estado_verificacion_id')
                ->constrained('estados_verificacion')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('tipo_documento_identidad_id')
                ->constrained('tipos_documento_identidad')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->string('numero_documento', 50);
            $table->string('ruta_documento_anverso', 255);
            $table->string('ruta_documento_reverso', 255)->nullable();
            $table->string('ruta_selfie', 255)->nullable();

            $table->timestamp('fecha_verificacion')->nullable();
            $table->text('notas_revision')->nullable();

            $table->foreignId('revisado_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verificaciones_usuario');
    }
};
