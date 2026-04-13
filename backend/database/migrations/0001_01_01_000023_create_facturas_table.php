<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Documento comercial/fiscal visible; separado del registro técnico RRSIF.
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('cliente_id')->constrained('clientes')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('tipo_factura_id')->constrained('tipos_factura')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('estado_factura_id')->constrained('estados_factura')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('factura_rectificada_id')->nullable()->constrained('facturas')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('tipo_rectificacion_id')->nullable()->constrained('tipos_rectificacion')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('motivo_rectificacion', 500)->nullable();

            $table->string('serie', 20)->default('A');
            $table->string('numero', 50);
            $table->date('fecha_emision');
            $table->date('fecha_operacion')->nullable();
            $table->string('moneda', 3)->default('EUR');

            // Snapshot fiscal inmutable de emisor/receptor.
            $table->string('emisor_nif', 20);
            $table->string('emisor_nombre_razon_social', 150);
            $table->string('emisor_domicilio_fiscal', 255);
            $table->string('receptor_nif', 20);
            $table->string('receptor_nombre_razon_social', 150);
            $table->string('receptor_domicilio_fiscal', 255);
            $table->string('receptor_cp', 15)->nullable();
            $table->string('receptor_municipio', 100)->nullable();
            $table->string('receptor_provincia', 100)->nullable();
            $table->string('receptor_pais', 100)->nullable();

            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->decimal('cuota_iva', 12, 2)->default(0.00);
            $table->decimal('total', 12, 2)->default(0.00);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['empresa_id', 'serie', 'numero']);
            $table->index(['empresa_id', 'fecha_emision']);
            $table->index(['cliente_id', 'fecha_emision']);
            $table->index('estado_factura_id');
            $table->index('tipo_factura_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};
