<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            if (!Schema::hasColumn('clientes', 'direccion')) {
                $table->string('direccion')->nullable()->after('notas');
            }
            if (!Schema::hasColumn('clientes', 'direccion_linea_2')) {
                $table->string('direccion_linea_2')->nullable()->after('direccion');
            }
            if (!Schema::hasColumn('clientes', 'ciudad')) {
                $table->string('ciudad')->nullable()->after('direccion_linea_2');
            }
            if (!Schema::hasColumn('clientes', 'provincia')) {
                $table->string('provincia')->nullable()->after('ciudad');
            }
            if (!Schema::hasColumn('clientes', 'codigo_postal')) {
                $table->string('codigo_postal', 15)->nullable()->after('provincia');
            }
            if (!Schema::hasColumn('clientes', 'pais')) {
                $table->string('pais', 100)->nullable()->default('España')->after('codigo_postal');
            }
        });

        if (Schema::hasColumn('ordenes_trabajo', 'localizacion_cliente_id')) {
            Schema::table('ordenes_trabajo', function (Blueprint $table) {
                $table->dropConstrainedForeignId('localizacion_cliente_id');
            });
        }

        Schema::dropIfExists('localizaciones_cliente');
        Schema::dropIfExists('tipos_localizacion_cliente');
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['direccion', 'direccion_linea_2', 'ciudad', 'provincia', 'codigo_postal', 'pais']);
        });
    }
};
