<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('solicitudes_verificacion', function (Blueprint $table): void {
            if (! Schema::hasColumn('solicitudes_verificacion', 'estado_identidad')) {
                $table->string('estado_identidad', 40)->default('pendiente')->after('estado_verificacion_id');
            }

            if (! Schema::hasColumn('solicitudes_verificacion', 'estado_empresa')) {
                $table->string('estado_empresa', 40)->default('pendiente')->after('estado_identidad');
            }

            if (! Schema::hasColumn('solicitudes_verificacion', 'estado_representacion')) {
                $table->string('estado_representacion', 40)->nullable()->after('estado_empresa');
            }

            if (! Schema::hasColumn('solicitudes_verificacion', 'motivo_rechazo')) {
                $table->text('motivo_rechazo')->nullable()->after('observaciones');
            }
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_verificacion', function (Blueprint $table): void {
            foreach (['estado_identidad', 'estado_empresa', 'estado_representacion', 'motivo_rechazo'] as $column) {
                if (Schema::hasColumn('solicitudes_verificacion', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
