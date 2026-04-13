<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modulos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 60)->unique();
            $table->string('nombre', 120);
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->unsignedInteger('orden_visual')->default(1);
            $table->string('icono', 120)->nullable();
            $table->timestamps();

            $table->index(['activo', 'orden_visual']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modulos');
    }
};
