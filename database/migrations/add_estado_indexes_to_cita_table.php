<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('cita_medica')) {
            Schema::table('cita_medica', function (Blueprint $t) {
                if (!Schema::hasColumn('cita_medica', 'estado')) {
                    $t->enum('estado', ['pendiente', 'confirmada', 'cancelada', 'completada'])
                        ->default('pendiente')->after('trata_cons');
                }
                // ensure indexes exist
                try { $t->index('estado'); } catch (\Throwable $e) { /* ignore */ }
                try { $t->index('fech_cons'); } catch (\Throwable $e) { /* ignore */ }
            });
        }
    }
    public function down(): void {
        if (Schema::hasTable('cita_medica')) {
            Schema::table('cita_medica', function (Blueprint $t) {
                try { $t->dropIndex(['estado']); } catch (\Throwable $e) { /* ignore */ }
                try { $t->dropIndex(['fech_cons']); } catch (\Throwable $e) { /* ignore */ }
                if (Schema::hasColumn('cita_medica', 'estado')) {
                    $t->dropColumn('estado');
                }
            });
        }
    }
};
