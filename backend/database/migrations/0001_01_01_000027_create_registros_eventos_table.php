<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Eventos técnicos de integridad/inalterabilidad/trazabilidad.
        Schema::create('registros_evento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('tipo_evento_facturacion_id')->constrained('tipos_evento_facturacion')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('modo_remision_facturacion_id')->constrained('modos_remision_facturacion')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('codigo_evento', 80);
            $table->string('descripcion', 255)->nullable();
            $table->timestampTz('generado_at');
            $table->string('hash_actual', 255);
            $table->string('hash_anterior_64', 64)->nullable();
            $table->longText('xml_contenido');
            $table->string('xml_version', 20);
            $table->timestamps();

            $table->index(['empresa_id', 'generado_at']);
            $table->index('tipo_evento_facturacion_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registros_evento');
    }
};
