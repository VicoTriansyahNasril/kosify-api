<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Http\Requests\Transaction\UpdateTransactionStatusRequest;
use App\Http\Resources\TransactionResource;
use App\Models\BoardingHouse;
use App\Models\Transaction;
use App\Services\TransactionService;

class TransactionController extends Controller
{
    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function index(BoardingHouse $boardingHouse)
    {
        $this->authorize('view', $boardingHouse);

        $transactions = Transaction::forBoardingHouse($boardingHouse->id)
            ->with(['tenant', 'room'])
            ->latest()
            ->paginate(20);

        return TransactionResource::collection($transactions);
    }

    public function store(StoreTransactionRequest $request)
    {
        $transaction = $this->transactionService->createManualTransaction($request->validated());

        return new TransactionResource($transaction);
    }

    public function updateStatus(UpdateTransactionStatusRequest $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        if ($request->status === 'paid') {
            $this->transactionService->markAsPaid($transaction);
        } else {
            $transaction->update($request->validated());
        }

        return new TransactionResource($transaction);
    }

    public function destroy(Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        if ($transaction->status !== \App\Enums\TransactionStatus::UNPAID) {
            return response()->json(['message' => 'Hanya transaksi status Unpaid yang bisa dihapus.'], 403);
        }

        $transaction->delete();

        return response()->json(['message' => 'Transaksi berhasil dihapus.']);
    }
}