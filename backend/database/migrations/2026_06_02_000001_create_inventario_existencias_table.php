<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventario_existencias', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('inventario_item_id')->constrained('inventario_items')->cascadeOnDelete();
            $table->foreignId('ubicacion_id')->constrained('inventario_ubicaciones')->restrictOnDelete();
            $table->decimal('cantidad', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['inventario_item_id', 'ubicacion_id']);
            $table->index(['empresa_id', 'ubicacion_id']);
        });

        $now = now();

        DB::table('inventario_items')
            ->select(['id', 'empresa_id', 'ubicacion_id', 'stock_actual'])
            ->where('stock_actual', '>', 0)
            ->orderBy('id')
            ->get()
            ->each(function (object $item) use ($now): void {
                $ubicacionId = $item->ubicacion_id;

                if ($ubicacionId === null) {
                    $ubicacionId = DB::table('inventario_ubicaciones')->where([
                        'empresa_id' => $item->empresa_id,
                        'nombre' => 'Sin ubicacion',
                    ])->value('id');

                    if ($ubicacionId === null) {
                        $ubicacionId = DB::table('inventario_ubicaciones')->insertGetId([
                            'empresa_id' => $item->empresa_id,
                            'nombre' => 'Sin ubicacion',
                            'descripcion' => 'Ubicacion creada automaticamente para stock existente sin ubicacion.',
                            'observaciones' => null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }

                    DB::table('inventario_items')
                        ->where('id', $item->id)
                        ->update(['ubicacion_id' => $ubicacionId, 'updated_at' => $now]);
                }

                DB::table('inventario_existencias')->insert([
                    'empresa_id' => $item->empresa_id,
                    'inventario_item_id' => $item->id,
                    'ubicacion_id' => $ubicacionId,
                    'cantidad' => $item->stock_actual,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_existencias');
    }
};
