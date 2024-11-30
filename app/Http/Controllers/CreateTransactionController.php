<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Support\Str;

class CreateTransactionController extends Controller
{
    public function __invoke(CreateTransactionRequest $createTransactionRequest): TransactionResource
    {
        $transaction = new Transaction();

        $transaction->transaction_id = Str::uuid();
        $transaction->status = Transaction::APPROVED;
        $transaction->fill($createTransactionRequest->validated());

        $transaction->save();

        return new TransactionResource($transaction);
    }
}
