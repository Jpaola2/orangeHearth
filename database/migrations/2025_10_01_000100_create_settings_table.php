<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Optional seeding: allowed NIT from env, if provided
        $envNit = env('ALLOWED_NIT');
        if (!empty($envNit)) {
            DB::table('settings')->updateOrInsert(
                ['key' => 'allowed_nit'],
                ['value' => $envNit, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

