<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla tokens.
     */
    public function up(): void
    {
        Schema::create('tokens', function (Blueprint $table) {
            $table->id();
            // Si se borra el usuario, se borra también su token
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('token')->unique();
            $table->timestamps();
        });
    }

    /**
     * Elimina la tabla tokens.
     */
    public function down(): void
    {
        Schema::dropIfExists('tokens');
    }
};
