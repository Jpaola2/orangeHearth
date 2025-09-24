<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mascota')) {
            Schema::table('mascota', function (Blueprint $table) {
                if (!Schema::hasColumn('mascota', 'edad_masc')) {
                    $table->unsignedInteger('edad_masc')->nullable()->after('gene_masc');
                }
                if (!Schema::hasColumn('mascota', 'unidad_edad')) {
                    $table->string('unidad_edad', 10)->nullable()->after('edad_masc');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('mascota')) {
            Schema::table('mascota', function (Blueprint $table) {
                if (Schema::hasColumn('mascota', 'edad_masc')) {
                    $table->dropColumn('edad_masc');
                }
                if (Schema::hasColumn('mascota', 'unidad_edad')) {
                    $table->dropColumn('unidad_edad');
                }
            });
        }
    }
};

