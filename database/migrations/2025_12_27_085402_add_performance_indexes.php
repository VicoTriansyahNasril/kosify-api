<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. BOARDING HOUSES
        Schema::table('boarding_houses', function (Blueprint $table) {
            // Ambil daftar index yang sudah ada di tabel ini
            $indexes = collect(Schema::getIndexes('boarding_houses'))->pluck('name')->toArray();

            // Cek satu per satu, buat jika belum ada
            if (!in_array('boarding_houses_name_index', $indexes)) {
                $table->index('name');
            }
            if (!in_array('boarding_houses_slug_index', $indexes)) {
                $table->index('slug');
            }
            if (!in_array('boarding_houses_category_index', $indexes)) {
                $table->index('category');
            }

            // Cek Fulltext Index (Penyebab Error sebelumnya)
            if (!in_array('boarding_houses_name_address_description_fulltext', $indexes)) {
                $table->fullText(['name', 'address', 'description']);
            }
        });

        // 2. ROOMS
        Schema::table('rooms', function (Blueprint $table) {
            $indexes = collect(Schema::getIndexes('rooms'))->pluck('name')->toArray();

            if (!in_array('rooms_name_index', $indexes)) {
                $table->index('name');
            }
            if (!in_array('rooms_price_index', $indexes)) {
                $table->index('price');
            }
            if (!in_array('rooms_status_index', $indexes)) {
                $table->index('status');
            }
            if (!in_array('rooms_boarding_house_id_status_index', $indexes)) {
                $table->index(['boarding_house_id', 'status']);
            }
        });

        // 3. TENANTS
        Schema::table('tenants', function (Blueprint $table) {
            $indexes = collect(Schema::getIndexes('tenants'))->pluck('name')->toArray();

            if (!in_array('tenants_name_index', $indexes)) {
                $table->index('name');
            }
            if (!in_array('tenants_phone_index', $indexes)) {
                $table->index('phone');
            }
            if (!in_array('tenants_due_date_index', $indexes)) {
                $table->index('due_date');
            }
        });

        // 4. TRANSACTIONS
        Schema::table('transactions', function (Blueprint $table) {
            $indexes = collect(Schema::getIndexes('transactions'))->pluck('name')->toArray();

            if (!in_array('transactions_invoice_number_index', $indexes)) {
                $table->index('invoice_number');
            }
            if (!in_array('transactions_status_index', $indexes)) {
                $table->index('status');
            }
            if (!in_array('transactions_due_date_index', $indexes)) {
                $table->index('due_date');
            }
            if (!in_array('transactions_type_index', $indexes)) {
                $table->index('type');
            }
        });
    }

    public function down(): void
    {
        // Saat rollback, kita coba drop index. Gunakan try-catch agar tidak error jika index sudah hilang.

        Schema::table('boarding_houses', function (Blueprint $table) {
            try {
                $table->dropIndex(['name']);
            } catch (\Exception $e) {
            }
            try {
                $table->dropIndex(['slug']);
            } catch (\Exception $e) {
            }
            try {
                $table->dropIndex(['category']);
            } catch (\Exception $e) {
            }
            try {
                $table->dropFullText(['name', 'address', 'description']);
            } catch (\Exception $e) {
            }
        });

        Schema::table('rooms', function (Blueprint $table) {
            try {
                $table->dropIndex(['name']);
            } catch (\Exception $e) {
            }
            try {
                $table->dropIndex(['price']);
            } catch (\Exception $e) {
            }
            try {
                $table->dropIndex(['status']);
            } catch (\Exception $e) {
            }
            try {
                $table->dropIndex(['boarding_house_id', 'status']);
            } catch (\Exception $e) {
            }
        });

        Schema::table('tenants', function (Blueprint $table) {
            try {
                $table->dropIndex(['name']);
            } catch (\Exception $e) {
            }
            try {
                $table->dropIndex(['phone']);
            } catch (\Exception $e) {
            }
            try {
                $table->dropIndex(['due_date']);
            } catch (\Exception $e) {
            }
        });

        Schema::table('transactions', function (Blueprint $table) {
            try {
                $table->dropIndex(['invoice_number']);
            } catch (\Exception $e) {
            }
            try {
                $table->dropIndex(['status']);
            } catch (\Exception $e) {
            }
            try {
                $table->dropIndex(['due_date']);
            } catch (\Exception $e) {
            }
            try {
                $table->dropIndex(['type']);
            } catch (\Exception $e) {
            }
        });
    }
};