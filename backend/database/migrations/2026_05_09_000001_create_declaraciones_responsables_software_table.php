<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('declaraciones_responsables_software')) {
            return;
        }

        Schema::create('declaraciones_responsables_software', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre_software', 120);
            $table->string('codigo_software', 60);
            $table->string('version_software', 50);
            $table->string('productor_nombre', 150);
            $table->string('productor_nif', 20)->nullable();
            $table->string('productor_direccion', 255)->nullable();
            $table->text('descripcion')->nullable();
            $table->json('componentes')->nullable();
            $table->boolean('modo_verifactu')->default(false);
            $table->boolean('permite_multiples_obligados')->default(true);
            $table->date('fecha_declaracion');
            $table->string('lugar_declaracion', 120)->nullable();
            $table->text('texto_declaracion');
            $table->string('hash_declaracion', 64)->unique();
            $table->string('pdf_path')->nullable();
            $table->boolean('activa')->default(true);
            $table->json('metadatos')->nullable();
            $table->timestamps();

            $table->unique(['codigo_software', 'version_software']);
            $table->index('activa');
            $table->index('fecha_declaracion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('declaraciones_responsables_software');
    }
};
