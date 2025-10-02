<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_completo', 120);
            $table->string('email', 160)->unique();
            $table->string('telefono', 20)->nullable();
            $table->string('cedula', 10)->unique();
            $table->string('empresa_nombre', 160);
            // NIT ya no es único por admin; todos comparten el mismo NIT permitido
            $table->string('nit', 20);
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();

            $table->index('cedula');
            $table->index('nit');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
