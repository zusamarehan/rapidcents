<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Services\CardValidatorService;
use Illuminate\Support\Str;

class CreateTransactionController extends Controller
{
    public function __invoke(CreateTransactionRequest $createTransactionRequest, CardValidatorService $cardValidatorService): TransactionResource
    {
        $transaction = new Transaction();

        $transaction->transaction_id = Str::uuid();
        $transaction->fill($createTransactionRequest->validated());

        $transaction->status = $cardValidatorService->validate($transaction);

        $transaction->save();

        return new TransactionResource($transaction);
    }
}
