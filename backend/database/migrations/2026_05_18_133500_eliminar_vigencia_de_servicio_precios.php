<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('servicio_precios', function (Blueprint $table): void {
            if (Schema::hasColumn('servicio_precios', 'vigente_desde')) {
                $table->dropIndex('servicio_precios_servicio_tipo_vigente_index');
            }

            if (Schema::hasColumn('servicio_precios', 'vigente_hasta')) {
                $table->dropIndex(['servicio_id', 'vigente_hasta']);
            }

            if (Schema::hasColumn('servicio_precios', 'vigente_desde')) {
                $table->dropColumn('vigente_desde');
            }

            if (Schema::hasColumn('servicio_precios', 'vigente_hasta')) {
                $table->dropColumn('vigente_hasta');
            }
        });
    }

    public function down(): void
    {
        Schema::table('servicio_precios', function (Blueprint $table): void {
            if (! Schema::hasColumn('servicio_precios', 'vigente_desde')) {
                $table->dateTime('vigente_desde')->useCurrent();
            }

            if (! Schema::hasColumn('servicio_precios', 'vigente_hasta')) {
                $table->dateTime('vigente_hasta')->nullable();
            }

            $table->index(['servicio_id', 'tipo_tarifa_servicio_id', 'vigente_desde'], 'servicio_precios_servicio_tipo_vigente_index');
            $table->index(['servicio_id', 'vigente_hasta']);
        });
    }
};
