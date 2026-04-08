<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventario_movimientos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('inventario_item_id')->constrained('inventario_items')->cascadeOnDelete();

            $table->foreignId('ubicacion_origen_id')->nullable()->constrained('inventario_ubicaciones')->nullOnDelete();
            $table->foreignId('ubicacion_destino_id')->nullable()->constrained('inventario_ubicaciones')->nullOnDelete();

            $table->enum('tipo_movimiento', ['entrada', 'salida', 'ajuste', 'traslado']);
            $table->decimal('cantidad', 12, 2);
            $table->decimal('stock_anterior', 12, 2);
            $table->decimal('stock_posterior', 12, 2);
            $table->text('motivo')->nullable();
            $table->timestamp('fecha_movimiento');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['empresa_id', 'tipo_movimiento']);
            $table->index(['inventario_item_id', 'fecha_movimiento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_movimientos');
    }
};
