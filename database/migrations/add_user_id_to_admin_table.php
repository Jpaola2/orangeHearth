<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('administrador') && !Schema::hasColumn('administrador', 'user_id')) {
            Schema::table('administrador', function (Blueprint $t) {
                $t->unsignedBigInteger('user_id')->nullable()->after('password');
                $t->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            });
        }
    }
    public function down(): void {
        if (Schema::hasTable('administrador') && Schema::hasColumn('administrador', 'user_id')) {
            Schema::table('administrador', function (Blueprint $t) {
                try { $t->dropForeign(['user_id']); } catch (\Throwable $e) { /* ignore */ }
                $t->dropColumn('user_id');
            });
        }
    }
};
