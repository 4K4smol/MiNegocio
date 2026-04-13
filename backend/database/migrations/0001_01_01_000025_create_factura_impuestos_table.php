<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Desglose fiscal por tipo impositivo para escenarios complejos.
        Schema::create('factura_impuestos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained('facturas')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('impuesto_codigo', 30);
            $table->string('impuesto_nombre', 100);
            $table->decimal('base_imponible', 12, 2)->default(0.00);
            $table->decimal('tipo_porcentaje', 5, 2)->default(0.00);
            $table->decimal('cuota', 12, 2)->default(0.00);
            $table->boolean('es_exento')->default(false);
            $table->string('motivo_exencion', 255)->nullable();
            $table->boolean('es_no_sujeto')->default(false);
            $table->string('motivo_no_sujecion', 255)->nullable();
            $table->decimal('recargo_equivalencia_porcentaje', 5, 2)->default(0.00);
            $table->decimal('recargo_equivalencia_cuota', 12, 2)->default(0.00);
            $table->string('calificacion', 50)->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamps();

            $table->unique(['factura_id', 'impuesto_codigo', 'tipo_porcentaje', 'calificacion', 'es_exento', 'es_no_sujeto'], 'factura_impuestos_unique_fiscal');
            $table->index('factura_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factura_impuestos');
    }
};
