<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Snapshot fiscal final por línea.
        Schema::create('factura_lineas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained('facturas')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('orden_trabajo_linea_id')->nullable()->constrained('orden_trabajo_lineas')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('unidad_servicio_id')->constrained('unidades_servicio')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('unidad_servicio_codigo', 30)->nullable();
            $table->string('unidad_servicio_nombre_snapshot', 100)->nullable();
            $table->string('servicio_nombre_snapshot', 120)->nullable();
            $table->text('descripcion');
            $table->decimal('cantidad', 10, 2)->default(1.00);
            $table->decimal('precio_unitario', 10, 2)->default(0.00);
            $table->decimal('base_imponible', 10, 2)->default(0.00);
            $table->decimal('iva_porcentaje', 5, 2)->default(21.00);
            $table->decimal('descuento_porcentaje', 5, 2)->default(0.00);
            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->decimal('total_iva', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2)->default(0.00);
            $table->integer('orden')->default(1);
            $table->timestamps();

            $table->index('factura_id');
            $table->index('orden_trabajo_linea_id');
            $table->index('unidad_servicio_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factura_lineas');
    }
};
