<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('boarding_houses', function (Blueprint $table) {
            $table->string('category')->default('campur')->after('slug');
            $table->json('rules')->nullable()->after('facilities');
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('boarding_houses', function (Blueprint $table) {
            $table->dropColumn(['category', 'rules', 'latitude', 'longitude']);
        });
    }
};