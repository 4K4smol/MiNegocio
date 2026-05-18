<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('inventario_ubicaciones', function (Blueprint $table): void {
            if (Schema::hasColumn('inventario_ubicaciones', 'direccion')) {
                $table->dropColumn('direccion');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventario_ubicaciones', function (Blueprint $table): void {
            if (! Schema::hasColumn('inventario_ubicaciones', 'direccion')) {
                $table->string('direccion')->nullable()->after('descripcion');
            }
        });
    }
};
