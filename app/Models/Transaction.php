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
}
