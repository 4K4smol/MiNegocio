<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('registros_facturacion', function (Blueprint $table): void {
            $table->string('estado_remision')->default('pendiente')->nullable()->after('respuesta_aeat');
            $table->unsignedInteger('intentos_remision')->default(0)->after('estado_remision');
            $table->timestamp('ultimo_intento_at')->nullable()->after('intentos_remision');
            $table->string('codigo_error_aeat')->nullable()->after('ultimo_intento_at');
            $table->text('descripcion_error_aeat')->nullable()->after('codigo_error_aeat');
        });
    }

    public function down(): void
    {
        Schema::table('registros_facturacion', function (Blueprint $table): void {
            $table->dropColumn(['estado_remision', 'intentos_remision', 'ultimo_intento_at', 'codigo_error_aeat', 'descripcion_error_aeat']);
        });
    }
};
