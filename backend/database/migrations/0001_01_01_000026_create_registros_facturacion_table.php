<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Registro técnico RRSIF / Veri*Factu (alta o anulación) encadenado por hash.
        Schema::create('registros_facturacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained('facturas')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('tipo_registro_facturacion_id')->constrained('tipos_registro_facturacion')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('modo_remision_facturacion_id')->constrained('modos_remision_facturacion')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('empresa_id')->constrained('empresas')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('emisor_nif', 20);
            $table->string('emisor_nombre_razon_social', 150);
            $table->string('serie', 20);
            $table->string('numero', 50);
            $table->date('fecha_expedicion');
            $table->foreignId('tipo_factura_id')->constrained('tipos_factura')->restrictOnDelete()->cascadeOnUpdate();
            $table->decimal('cuota_total', 12, 2)->default(0.00);
            $table->decimal('importe_total', 12, 2)->default(0.00);

            $table->boolean('primer_registro_cadena')->default(false);
            $table->string('registro_anterior_nif', 20)->nullable();
            $table->string('registro_anterior_serie', 20)->nullable();
            $table->string('registro_anterior_numero', 50)->nullable();
            $table->date('registro_anterior_fecha_expedicion')->nullable();
            $table->string('registro_anterior_hash_64', 64)->nullable();

            $table->string('tipo_huella', 30)->default('sha256');
            $table->string('hash_actual', 255);
            $table->text('firma_electronica')->nullable();
            $table->timestampTz('generado_at');
            $table->longText('xml_contenido');
            $table->string('xml_version', 20);
            $table->string('codigo_sistema_informatico', 50);
            $table->string('id_sistema_informatico', 60)->nullable();
            $table->string('nombre_sistema', 120);
            $table->string('version_sistema', 50);
            $table->string('numero_instalacion', 50);
            $table->boolean('tipo_uso_posible_solo_verifactu')->default(false);
            $table->boolean('tipo_uso_posible_multi_ot')->default(false);
            $table->boolean('indicador_multiples_ot')->default(false);
            $table->string('productor_nif', 20);
            $table->string('productor_nombre', 150);
            $table->timestampTz('enviado_aeat_at')->nullable();
            $table->longText('respuesta_aeat')->nullable();
            $table->timestamps();

            $table->index(['empresa_id', 'generado_at']);
            $table->index('hash_actual');
            $table->index(['serie', 'numero', 'fecha_expedicion']);
            $table->index(['registro_anterior_nif', 'registro_anterior_serie', 'registro_anterior_numero', 'registro_anterior_fecha_expedicion']);
            $table->index(['emisor_nif', 'serie', 'numero', 'fecha_expedicion']);
            $table->index('factura_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registros_facturacion');
    }
};
