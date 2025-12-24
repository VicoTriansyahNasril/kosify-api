<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('room_id')->constrained();
            
            $table->string('invoice_number')->unique();
            
            $table->string('type')->default('rent');
            
            $table->decimal('amount', 12, 0);
        
            $table->string('status')->default('unpaid')->index();
            
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            
            $table->string('payment_proof')->nullable();
            
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};