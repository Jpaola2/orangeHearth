<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            if (Schema::hasColumn('admins', 'password')) {
                $table->dropColumn('password');
            }
            if (!Schema::hasColumn('admins', 'user_id')) {
                $table->foreignId('user_id')->after('id')->constrained('users')->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            if (Schema::hasColumn('admins', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
            if (!Schema::hasColumn('admins', 'password')) {
                $table->string('password')->nullable();
            }
        });
    }
};

