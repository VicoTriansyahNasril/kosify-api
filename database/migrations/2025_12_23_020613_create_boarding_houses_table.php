<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boarding_houses', function (Blueprint $table) {
            $table->ulid('id')->primary();
            
            // Relasi ke Owner
            $table->foreignUlid('user_id')->constrained('users')->cascadeOnDelete();
            
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('address');
            $table->text('description')->nullable();
            
            $table->json('facilities')->nullable(); 
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boarding_houses');
    }
};