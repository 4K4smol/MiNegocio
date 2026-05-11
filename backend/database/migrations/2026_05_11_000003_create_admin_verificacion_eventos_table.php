<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('admin_verificacion_eventos')) {
            return;
        }

        Schema::create('admin_verificacion_eventos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->foreignId('solicitud_verificacion_id')->nullable()->constrained('solicitudes_verificacion')->cascadeOnDelete();
            $table->string('accion', 120);
            $table->string('estado_anterior', 80)->nullable();
            $table->string('estado_nuevo', 80)->nullable();
            $table->text('motivo')->nullable();
            $table->json('metadatos')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_verificacion_eventos');
    }
};
