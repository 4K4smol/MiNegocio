<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordenes_trabajo', function (Blueprint $table): void {
            if (!Schema::hasColumn('ordenes_trabajo', 'localizacion_cliente_id')) {
                $table->foreignId('localizacion_cliente_id')
                    ->nullable()
                    ->after('cliente_id')
                    ->constrained('localizaciones_cliente')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();
            }
        });
    }

    public function down(): void
    {
        Schema::table('ordenes_trabajo', function (Blueprint $table): void {
            if (Schema::hasColumn('ordenes_trabajo', 'localizacion_cliente_id')) {
                $table->dropConstrainedForeignId('localizacion_cliente_id');
            }
        });
    }
};
