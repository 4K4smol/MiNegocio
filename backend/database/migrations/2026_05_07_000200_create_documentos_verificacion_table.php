<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('documentos_verificacion')) { return; }
        Schema::create('documentos_verificacion', function (Blueprint $table): void {
            $table->id();
            $table->string('disco', 50);
            $table->string('ruta', 500);
            $table->string('nombre_original', 255);
            $table->string('mime_type', 150)->nullable();
            $table->string('extension', 20)->nullable();
            $table->unsignedBigInteger('tamano')->default(0);
            $table->string('hash_sha256', 64)->nullable();
            $table->string('tipo_documento', 60);
            $table->foreignId('estado_verificacion_id')->constrained('estados_verificacion');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->foreignId('verificacion_usuario_id')->nullable()->constrained('verificaciones_usuario')->nullOnDelete();
            $table->foreignId('verificacion_empresa_id')->nullable()->constrained('verificaciones_empresa')->nullOnDelete();
            $table->foreignId('subido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('revisado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_revision')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('documentos_verificacion'); }
};
