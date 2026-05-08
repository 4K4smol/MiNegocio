<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('solicitudes_verificacion')) {
            return;
        }

        Schema::create('solicitudes_verificacion', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('empresa_id')
                ->nullable()
                ->constrained('empresas')
                ->nullOnDelete();

            $table->foreignId('estado_verificacion_id')
                ->constrained('estados_verificacion');

            $table->foreignId('revisado_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('observaciones')->nullable();
            $table->timestamp('fecha_revision')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_verificacion');
    }
};
