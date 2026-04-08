<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventario_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('categoria_id')->constrained('inventario_categorias')->restrictOnDelete();
            $table->foreignId('unidad_medida_id')->constrained('inventario_unidades_medida')->restrictOnDelete();
            $table->foreignId('ubicacion_id')->nullable()->constrained('inventario_ubicaciones')->nullOnDelete();

            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('sku')->nullable(); // Stock Keeping Unit o Unidad de Mantenimiento de Existencias
            $table->string('codigo_barras')->nullable();
            $table->decimal('stock_actual', 12, 2)->default(0);
            $table->decimal('stock_minimo', 12, 2)->default(0);
            $table->decimal('coste_unitario', 12, 2)->nullable();
            $table->boolean('activo')->default(true);

            $table->softDeletes();
            $table->timestamps();

            $table->unique(['empresa_id', 'sku']);
            $table->index(['empresa_id', 'nombre']);
            $table->index(['empresa_id', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_items');
    }
};
