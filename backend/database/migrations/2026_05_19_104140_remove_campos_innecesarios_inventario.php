<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventario_items', function (Blueprint $table) {
            $table->dropUnique('inventario_items_empresa_id_sku_unique');
            $table->dropIndex('inventario_items_empresa_id_activo_index');
            $table->dropColumn(['sku', 'codigo_barras', 'coste_unitario', 'activo']);
        });

        Schema::table('inventario_ubicaciones', function (Blueprint $table) {
            $table->dropIndex('inventario_ubicaciones_empresa_id_activo_index');
            $table->dropColumn('activo');
        });
    }

    public function down(): void
    {
        Schema::table('inventario_items', function (Blueprint $table) {
            $table->string('sku')->nullable();
            $table->string('codigo_barras')->nullable();
            $table->decimal('coste_unitario', 12, 2)->nullable();
            $table->boolean('activo')->default(true);
            $table->unique(['empresa_id', 'sku']);
            $table->index(['empresa_id', 'activo']);
        });

        Schema::table('inventario_ubicaciones', function (Blueprint $table) {
            $table->boolean('activo')->default(true);
            $table->index(['empresa_id', 'activo']);
        });
    }
};
