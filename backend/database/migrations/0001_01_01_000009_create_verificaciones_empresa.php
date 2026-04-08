<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verificaciones_empresa', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')
                ->unique()
                ->constrained('empresas')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('estado_verificacion_id')
                ->constrained('estados_verificacion')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->string('ruta_documento_fiscal', 255)->nullable();
            $table->string('ruta_registro_mercantil', 255)->nullable();
            $table->string('ruta_documento_representacion', 255)->nullable();
            $table->string('ruta_poder_apoderamiento', 255)->nullable();
            $table->string('referencia_certificado_digital', 255)->nullable();

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
        Schema::dropIfExists('verificaciones_empresa');
    }
};
