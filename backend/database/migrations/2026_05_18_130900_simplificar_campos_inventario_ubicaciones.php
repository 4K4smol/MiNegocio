<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('inventario_ubicaciones', function (Blueprint $table): void {
            if (!Schema::hasColumn('inventario_ubicaciones', 'observaciones')) {
                $table->text('observaciones')->nullable()->after('descripcion');
            }

            if (Schema::hasColumn('inventario_ubicaciones', 'tipo')) {
                $table->dropColumn('tipo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventario_ubicaciones', function (Blueprint $table): void {
            if (!Schema::hasColumn('inventario_ubicaciones', 'tipo')) {
                $table->string('tipo', 50)->nullable()->after('descripcion');
            }

            if (Schema::hasColumn('inventario_ubicaciones', 'observaciones')) {
                $table->dropColumn('observaciones');
            }
        });
    }
};
