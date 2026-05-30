<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('orden_trabajo_contadores');

        if (Schema::hasTable('ordenes_trabajo')) {
            if (DB::getDriverName() === 'pgsql') {
                DB::statement('ALTER TABLE ordenes_trabajo ALTER COLUMN numero DROP NOT NULL');
            }

            if (!Schema::hasColumn('ordenes_trabajo', 'subtotal')) {
                DB::statement('ALTER TABLE ordenes_trabajo ADD COLUMN subtotal NUMERIC(12, 2) NOT NULL DEFAULT 0');
            }

            if (!Schema::hasColumn('ordenes_trabajo', 'iva_total')) {
                DB::statement('ALTER TABLE ordenes_trabajo ADD COLUMN iva_total NUMERIC(12, 2) NOT NULL DEFAULT 0');
            }

            if (!Schema::hasColumn('ordenes_trabajo', 'total')) {
                DB::statement('ALTER TABLE ordenes_trabajo ADD COLUMN total NUMERIC(12, 2) NOT NULL DEFAULT 0');
            }
        }
    }

    public function down(): void
    {
        // subtotal, iva_total y total existen en la migración base 0001_01_01_000023_1;
        // eliminarlos aquí rompería el schema al hacer rollback de esta migración sola.
        // orden_trabajo_contadores se eliminó intencionalmente y no se recrea.
    }
};
