<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionResource;
use App\Models\Transaction;

class RetrieveTransactionController extends Controller
{
    public function __invoke(string $transactionId)
    {
        $transaction = Transaction::query()
            ->where('transaction_id', $transactionId)
            ->firstOrFail();

        return new TransactionResource($transaction);
    }
}
