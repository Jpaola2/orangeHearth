<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('medico_veterinario') && !Schema::hasColumn('medico_veterinario', 'user_id')) {
            Schema::table('medico_veterinario', function (Blueprint $t) {
                $t->unsignedBigInteger('user_id')->nullable()->after('tarjeta_profesional_mv');
                $t->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            });
        }
    }
    public function down(): void {
        if (Schema::hasTable('medico_veterinario') && Schema::hasColumn('medico_veterinario', 'user_id')) {
            Schema::table('medico_veterinario', function (Blueprint $t) {
                try { $t->dropForeign(['user_id']); } catch (\Throwable $e) { /* ignore */ }
                $t->dropColumn('user_id');
            });
        }
    }
};
