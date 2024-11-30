<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Transaction extends Model
{
    use HasFactory;

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

    /**
     * Mutator to handle encryption when setting card
     */
    public function setCardNumberAttribute($value): void
    {
        $this->attributes['card_number'] = Crypt::encryptString($value);
    }

    /**
     * Accessor to handle decryption when getting email
     */
    public function getCardNumberAttribute(): string
    {
        return Crypt::decryptString($this->attributes['card_number']);
    }

    public function getAmount() : float
    {
        return $this->amount / 100;
    }

    public function isDuplicateTransaction(string $cardNumber): bool
    {
        $narrowFilter = Transaction::query()
            ->where('currency', $this->currency)
            ->where('amount', $this->amount)
            ->where('created_at', '>' , now()->subMinutes(10))
            ->get();

        $exists = $narrowFilter->first(function ($record) use ($cardNumber) {
            try {
                return $record->card_number === $cardNumber;
            } catch (\Exception $e) {
                return false;
            }
        });

        return (bool) $exists;
    }
}
