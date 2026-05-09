<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registros_evento_facturacion', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('factura_id')->nullable()->constrained('facturas')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('registro_facturacion_id')->nullable()->constrained('registros_facturacion')->nullOnDelete()->cascadeOnUpdate();
            $table->string('codigo_evento', 120);
            $table->string('descripcion')->nullable();
            $table->json('payload_json')->nullable();
            $table->string('ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestampTz('generado_at');
            $table->timestamps();

            $table->index(['empresa_id', 'generado_at']);
            $table->index('codigo_evento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registros_evento_facturacion');
    }
};
