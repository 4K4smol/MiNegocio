<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('documentos_verificacion')) {
            return;
        }
        Schema::create('documentos_verificacion', function (Blueprint $table) {
            $table->id();

            $table->foreignId('solicitud_verificacion_id')
                ->constrained('solicitudes_verificacion')
                ->cascadeOnDelete();

            $table->string('tipo_documento', 80);
            $table->string('archivo', 500);
            $table->string('nombre_original', 255);
            $table->string('mime_type', 150)->nullable();
            $table->unsignedBigInteger('tamano')->nullable();

            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('documentos_verificacion');
    }
};
