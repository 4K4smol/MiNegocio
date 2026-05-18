<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('documentos_verificacion', function (Blueprint $table): void {
            if (!Schema::hasColumn('documentos_verificacion', 'hash_sha256')) {
                $table->string('hash_sha256', 64)->nullable()->after('tamano');
            }
        });
    }

    public function down(): void
    {
        Schema::table('documentos_verificacion', function (Blueprint $table): void {
            if (Schema::hasColumn('documentos_verificacion', 'hash_sha256')) {
                $table->dropColumn('hash_sha256');
            }
        });
    }
};
