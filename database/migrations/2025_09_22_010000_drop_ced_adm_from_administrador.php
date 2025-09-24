<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('administrador') && Schema::hasColumn('administrador', 'ced_adm')) {
            Schema::table('administrador', function (Blueprint $table) {
                // En caso de existir índices/únicos sobre 'ced_adm', primero los removemos
                try { $table->dropUnique(['ced_adm']); } catch (\Throwable $e) { /* ignore if not unique */ }
                $table->dropColumn('ced_adm');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('administrador') && !Schema::hasColumn('administrador', 'ced_adm')) {
            Schema::table('administrador', function (Blueprint $table) {
                $table->string('ced_adm', 20)->nullable();
            });
        }
    }
};

