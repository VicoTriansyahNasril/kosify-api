<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Models\Tenant;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function createManualTransaction(array $data): Transaction
    {
        // Ambil data tenant untuk snapshot room_id
        $tenant = Tenant::findOrFail($data['tenant_id']);

        return Transaction::create([
            ...$data,
            'room_id' => $tenant->room_id,
            'status' => TransactionStatus::UNPAID,
        ]);
    }

    public function markAsPaid(Transaction $transaction): Transaction
    {
        $transaction->update([
            'status' => TransactionStatus::PAID,
            'paid_at' => now(),
        ]);

        return $transaction;
    }
}