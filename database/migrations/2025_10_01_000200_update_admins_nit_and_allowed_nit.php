<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Drop UNIQUE index on admins.nit if present, then ensure a normal index exists
        if (Schema::hasTable('admins')) {
            try {
                $uniqueIdx = DB::select('SHOW INDEX FROM admins WHERE Column_name = ? AND Non_unique = 0', ['nit']);
                foreach ($uniqueIdx as $idx) {
                    $name = $idx->Key_name ?? $idx->key_name ?? null;
                    if ($name) {
                        DB::statement('ALTER TABLE admins DROP INDEX `'.$name.'`');
                    }
                }
            } catch (\Throwable $e) {
                // Ignore if cannot inspect/drop index
            }

            try {
                $anyIdx = DB::select('SHOW INDEX FROM admins WHERE Column_name = ? AND Non_unique IN (0,1)', ['nit']);
                if (empty($anyIdx)) {
                    DB::statement('ALTER TABLE admins ADD INDEX `admins_nit_index` (`nit`)');
                }
            } catch (\Throwable $e) {
                // Ignore if index already exists or cannot be created
            }
        }

        // Upsert allowed NIT into settings
        if (Schema::hasTable('settings')) {
            DB::table('settings')->updateOrInsert(
                ['key' => 'allowed_nit'],
                ['value' => '22296', 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        // Recreate unique index on admins.nit (best-effort) and drop normal index
        if (Schema::hasTable('admins')) {
            try {
                // Drop normal index if exists
                $idx = DB::select('SHOW INDEX FROM admins WHERE Column_name = ? AND Key_name = ?', ['nit', 'admins_nit_index']);
                if (!empty($idx)) {
                    DB::statement('ALTER TABLE admins DROP INDEX `admins_nit_index`');
                }
            } catch (\Throwable $e) {
                // ignore
            }
            try {
                DB::statement('ALTER TABLE admins ADD UNIQUE `admins_nit_unique` (`nit`)');
            } catch (\Throwable $e) {
                // ignore
            }
        }

        // No-op for settings value on down
    }
};
