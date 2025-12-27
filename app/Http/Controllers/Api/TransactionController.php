<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Http\Requests\Transaction\UpdateTransactionStatusRequest;
use App\Http\Resources\TransactionResource;
use App\Models\BoardingHouse;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class TransactionController extends Controller
{
    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function index(Request $request, BoardingHouse $boardingHouse)
    {
        $this->authorize('view', $boardingHouse);

        $query = Transaction::forBoardingHouse($boardingHouse->id)
            ->with(['tenant', 'room']);

        if ($search = $request->input('q')) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('tenant', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        return TransactionResource::collection($query->latest()->paginate(20));
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