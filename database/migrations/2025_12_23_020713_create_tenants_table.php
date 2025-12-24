<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->ulid('id')->primary();
            
            $table->foreignUlid('room_id')->nullable()->constrained()->nullOnDelete();
            
            $table->string('name');
            $table->string('identification_number')->nullable();
            $table->string('phone')->index();
            $table->string('emergency_contact')->nullable();
            
            $table->date('entry_date');
            $table->date('due_date')->index();
            
            $table->string('status')->default('active'); 
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};