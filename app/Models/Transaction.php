<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    public const APPROVED = 'approved';
    public const DECLINED = 'declined';
    public const PENDING = 'pending';
    public const NSF = 'nsf';

    protected $casts = [
        'metadata' => 'array'
    ];

    protected $fillable = [
        'transaction_id',
        'card_number',
        'amount',
        'currency',
        'customer_email',
        'status',
        'metadata'
    ];

    public function getAmount() : float
    {
        return $this->amount / 100;
    }

    public function isDuplicateTransaction(): bool
    {
        return Transaction::query()
            ->where('currency', $this->currency)
            ->where('amount', $this->amount)
            ->where('card_number', $this->card_number)
            ->where('created_at', '>' , now()->subMinutes(10))
            ->exists();
    }
}
