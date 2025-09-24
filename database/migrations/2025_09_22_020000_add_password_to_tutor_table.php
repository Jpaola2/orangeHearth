<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tutor') && !Schema::hasColumn('tutor', 'password')) {
            Schema::table('tutor', function (Blueprint $table) {
                $table->string('password', 255)->nullable()->after('correo_tutor');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tutor') && Schema::hasColumn('tutor', 'password')) {
            Schema::table('tutor', function (Blueprint $table) {
                $table->dropColumn('password');
            });
        }
    }
};

