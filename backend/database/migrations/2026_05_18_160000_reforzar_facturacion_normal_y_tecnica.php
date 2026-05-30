<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estados_remision_facturacion', function (Blueprint $table): void {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->string('nombre', 100);
            $table->string('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();
        });

        Schema::create('secuencias_facturacion', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete()->cascadeOnUpdate();
            $table->unsignedSmallInteger('ejercicio');
            $table->string('serie', 20);
            $table->unsignedInteger('ultimo_numero')->default(0);
            $table->timestamps();

            $table->unique(['empresa_id', 'ejercicio', 'serie'], 'secuencias_facturacion_empresa_ejercicio_serie_unique');
        });

        Schema::create('empresa_series_facturacion', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('serie', 20);
            $table->string('nombre', 100);
            $table->string('descripcion')->nullable();
            $table->boolean('es_default')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['empresa_id', 'serie']);
            $table->index(['empresa_id', 'activo']);
        });

        Schema::create('empresa_facturacion_config', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('empresa_id')->unique()->constrained('empresas')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('serie_default', 20)->default('A');
            $table->string('modo_facturacion', 50)->default('mvp_interno');
            $table->boolean('emitir_desde_borrador')->default(true);
            $table->json('metadatos')->nullable();
            $table->timestamps();
        });

        Schema::table('facturas', function (Blueprint $table): void {
            if (!Schema::hasColumn('facturas', 'orden_trabajo_id')) {
                $table->foreignId('orden_trabajo_id')
                    ->nullable()
                    ->after('cliente_id')
                    ->constrained('ordenes_trabajo')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();
            }

            if (!Schema::hasColumn('facturas', 'numero_completo')) {
                $table->string('numero_completo', 80)->nullable()->after('numero');
            }

            if (!Schema::hasColumn('facturas', 'fecha_vencimiento')) {
                $table->date('fecha_vencimiento')->nullable()->after('fecha_operacion');
            }

            if (!Schema::hasColumn('facturas', 'base_imponible')) {
                $table->decimal('base_imponible', 12, 2)->default(0)->after('cuota_iva');
            }

            if (!Schema::hasColumn('facturas', 'total_iva')) {
                $table->decimal('total_iva', 12, 2)->default(0)->after('base_imponible');
            }

            if (!Schema::hasColumn('facturas', 'total_retencion')) {
                $table->decimal('total_retencion', 12, 2)->default(0)->after('total_iva');
            }

            if (!Schema::hasColumn('facturas', 'total_descuento')) {
                $table->decimal('total_descuento', 12, 2)->default(0)->after('total_retencion');
            }

            if (!Schema::hasColumn('facturas', 'metadatos')) {
                $table->json('metadatos')->nullable()->after('observaciones');
            }

            if (!Schema::hasColumn('facturas', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('observaciones_pago')->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            }

            if (!Schema::hasColumn('facturas', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            }

            if (!Schema::hasColumn('facturas', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE facturas ALTER COLUMN numero DROP NOT NULL');
            DB::statement('ALTER TABLE registros_facturacion ALTER COLUMN factura_id DROP NOT NULL');
        }

        Schema::table('factura_lineas', function (Blueprint $table): void {
            if (!Schema::hasColumn('factura_lineas', 'retencion_porcentaje')) {
                $table->decimal('retencion_porcentaje', 5, 2)->default(0)->after('iva_porcentaje');
            }

            if (!Schema::hasColumn('factura_lineas', 'cuota_retencion')) {
                $table->decimal('cuota_retencion', 10, 2)->default(0)->after('total_iva');
            }

            if (!Schema::hasColumn('factura_lineas', 'total_linea')) {
                $table->decimal('total_linea', 10, 2)->default(0)->after('cuota_retencion');
            }
        });

        Schema::table('factura_impuestos', function (Blueprint $table): void {
            if (!Schema::hasColumn('factura_impuestos', 'tipo_impuesto')) {
                $table->string('tipo_impuesto', 30)->default('IVA')->after('factura_id');
            }

            if (!Schema::hasColumn('factura_impuestos', 'base')) {
                $table->decimal('base', 12, 2)->default(0)->after('impuesto_nombre');
            }

            if (!Schema::hasColumn('factura_impuestos', 'porcentaje')) {
                $table->decimal('porcentaje', 5, 2)->default(0)->after('base_imponible');
            }
        });

        Schema::table('registros_facturacion', function (Blueprint $table): void {
            if (!Schema::hasColumn('registros_facturacion', 'estado_remision_facturacion_id')) {
                $table->foreignId('estado_remision_facturacion_id')
                    ->nullable()
                    ->after('modo_remision_facturacion_id')
                    ->constrained('estados_remision_facturacion')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();
            }

            if (!Schema::hasColumn('registros_facturacion', 'registro_anterior_id')) {
                $table->foreignId('registro_anterior_id')->nullable()->after('empresa_id')->constrained('registros_facturacion')->nullOnDelete()->cascadeOnUpdate();
            }

            if (!Schema::hasColumn('registros_facturacion', 'registro_anulado_id')) {
                $table->foreignId('registro_anulado_id')->nullable()->after('registro_anterior_id')->constrained('registros_facturacion')->nullOnDelete()->cascadeOnUpdate();
            }

            if (!Schema::hasColumn('registros_facturacion', 'numero_completo')) {
                $table->string('numero_completo', 80)->nullable()->after('numero');
            }

            if (!Schema::hasColumn('registros_facturacion', 'payload_json')) {
                $table->json('payload_json')->nullable()->after('hash_actual');
            }

            if (!Schema::hasColumn('registros_facturacion', 'qr_contenido')) {
                $table->text('qr_contenido')->nullable()->after('xml_version');
            }

            if (!Schema::hasColumn('registros_facturacion', 'remitido_at')) {
                $table->timestampTz('remitido_at')->nullable()->after('enviado_aeat_at');
            }
        });

        Schema::create('factura_cobros', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('factura_id')->constrained('facturas')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete()->cascadeOnUpdate();
            $table->date('fecha_cobro');
            $table->decimal('importe', 12, 2);
            $table->string('metodo_pago', 60);
            $table->string('referencia')->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();

            $table->index(['empresa_id', 'fecha_cobro']);
            $table->index('factura_id');
        });

        Schema::create('factura_historial', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('factura_id')->constrained('facturas')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->string('accion', 80);
            $table->foreignId('estado_anterior_id')->nullable()->constrained('estados_factura')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('estado_nuevo_id')->nullable()->constrained('estados_factura')->nullOnDelete()->cascadeOnUpdate();
            $table->text('descripcion')->nullable();
            $table->json('metadatos')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['empresa_id', 'created_at']);
            $table->index(['factura_id', 'created_at']);
            $table->index('accion');
        });

        Schema::create('factura_documentos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('factura_id')->constrained('facturas')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('tipo', 50)->default('pdf');
            $table->string('ruta');
            $table->string('nombre_original')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('tamano')->nullable();
            $table->string('hash_sha256', 64)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();

            $table->index(['empresa_id', 'tipo']);
            $table->index('factura_id');
        });
    }

    public function down(): void
    {
        // Primero eliminar FKs y columnas añadidas a tablas existentes,
        // antes de dropear las tablas referenciadas.

        Schema::table('registros_facturacion', function (Blueprint $table): void {
            if (Schema::hasColumn('registros_facturacion', 'estado_remision_facturacion_id')) {
                $table->dropConstrainedForeignId('estado_remision_facturacion_id');
            }
            if (Schema::hasColumn('registros_facturacion', 'registro_anterior_id')) {
                $table->dropConstrainedForeignId('registro_anterior_id');
            }
            if (Schema::hasColumn('registros_facturacion', 'registro_anulado_id')) {
                $table->dropConstrainedForeignId('registro_anulado_id');
            }
            foreach (['numero_completo', 'payload_json', 'qr_contenido', 'remitido_at'] as $col) {
                if (Schema::hasColumn('registros_facturacion', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('facturas', function (Blueprint $table): void {
            if (Schema::hasColumn('facturas', 'orden_trabajo_id')) {
                $table->dropConstrainedForeignId('orden_trabajo_id');
            }
            if (Schema::hasColumn('facturas', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }
            if (Schema::hasColumn('facturas', 'updated_by')) {
                $table->dropConstrainedForeignId('updated_by');
            }
            foreach (['numero_completo', 'fecha_vencimiento', 'base_imponible', 'total_iva',
                      'total_retencion', 'total_descuento', 'metadatos', 'deleted_at'] as $col) {
                if (Schema::hasColumn('facturas', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('factura_lineas', function (Blueprint $table): void {
            foreach (['retencion_porcentaje', 'cuota_retencion', 'total_linea'] as $col) {
                if (Schema::hasColumn('factura_lineas', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('factura_impuestos', function (Blueprint $table): void {
            foreach (['tipo_impuesto', 'base', 'porcentaje'] as $col) {
                if (Schema::hasColumn('factura_impuestos', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::dropIfExists('factura_documentos');
        Schema::dropIfExists('factura_historial');
        Schema::dropIfExists('factura_cobros');
        Schema::dropIfExists('empresa_facturacion_config');
        Schema::dropIfExists('empresa_series_facturacion');
        Schema::dropIfExists('secuencias_facturacion');
        Schema::dropIfExists('estados_remision_facturacion');
    }
};
