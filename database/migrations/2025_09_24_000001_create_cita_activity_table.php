<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cita_activity', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cita_id');
            $table->string('old_estado', 30)->nullable();
            $table->string('new_estado', 30)->nullable();
            $table->unsignedBigInteger('actor_user_id')->nullable();
            $table->string('actor_name', 150)->nullable();
            $table->string('actor_role', 20)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('cita_id');
            $table->index('actor_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cita_activity');
    }
};

