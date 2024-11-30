<?php

namespace App\Services;

use App\Models\Transaction;

class CardValidatorService
{
    protected array $cardRules = [
        '1234567890123456' => 'always_approved',
        '1111222233334444' => 'always_declined',
        '9876543210987654' => 'approved_in_usd',
        '5678901234567890' => 'declined_if_amount_less_50',
        '5432167890123456' => 'approved_if_divisible_by_10',
        '1234432112344321' => 'approved_in_cad',
        '6789012345678901' => 'declined_if_duplicate_transaction_in_10_minutes',
        '8888888888888888' => 'declined_if_missing_metadata',
        '3333333333333333' => 'always_pending',
        '1212121212121212' => 'approved_only_for_example.com',
        '2222222222222222' => 'declined_if_meta_contains_test_key',
        '9999999999999999' => 'nsf_if_amount_between_100_and_200',
        '1357913579135791' => 'approved_if_amount_is_in_even_numbers',
        '2468024680246802' => 'decline_if_amount_is_prime_number',
        '7777777777777777' => 'approved_in_eur_and_amount_is_greater_than_500',
        '6666666666666666' => 'declined_if_amount_ends_in_7',
        '9988776655443322' => 'approved_if_amount_is_less_than_or_equals_20',
        '2233445566778899' => 'declined_if_currency_in_usd',
        '3344556677889900' => 'approved_if_meta_contains_valid_key',
        '5566778899001122' => 'declined_if_divisible_by_3',
        '7788990011223344' => 'declined_if_transaction_after_8pm',
        '8899001122334455' => 'approved_only_if_currency_in_GBP_AUD',
        '9900112233445566' => 'declined_if_email_contains_test',
    ];

    public function validate(Transaction $transaction): string
    {
        if (!isset($this->cardRules[$transaction->card_number])) {
            return Transaction::DECLINED;
        }

        switch ($this->cardRules[$transaction->card_number]) {
            case 'always_approved':
                return Transaction::APPROVED;

            case 'approved_in_usd':
                return $transaction->currency === 'USD' ? Transaction::APPROVED : Transaction::DECLINED;

            case 'declined_if_amount_less_50':
                return $transaction->getAmount() >= 50 ? Transaction::APPROVED : Transaction::DECLINED;

            case 'approved_if_divisible_by_10':
                return $transaction->getAmount() % 10 === 0 ? Transaction::APPROVED : Transaction::DECLINED;

            case 'approved_in_cad':
                return $transaction->currency === 'CAD' ? Transaction::APPROVED : Transaction::DECLINED;

            case 'declined_if_duplicate_transaction_in_10_minutes':
                return $transaction->isDuplicateTransaction() ? Transaction::DECLINED : Transaction::APPROVED;

            case 'declined_if_missing_metadata':
                return !is_null($transaction->metadata) ? Transaction::APPROVED : Transaction::DECLINED;

            case 'always_pending':
                return Transaction::PENDING;

            case 'approved_only_for_example.com':
                return str_ends_with($transaction->customer_email, '@example.com') ? Transaction::APPROVED : Transaction::DECLINED;

            case 'declined_if_metadata_contains_test':
                return isset($metadata['test']) ? 'declined' : 'approved';

            case 'nsf_if_amount_between_100_and_200':
                return ($transaction->getAmount() >= 100 && $transaction->getAmount() <= 200) ? Transaction::NSF : Transaction::APPROVED;

            case 'approved_if_amount_is_in_even_numbers':
                return $transaction->getAmount() % 2 === 0 ? Transaction::APPROVED : Transaction::DECLINED;

            case 'decline_if_amount_is_prime_number':
                return self::isPrime($transaction->getAmount()) ? Transaction::DECLINED : Transaction::APPROVED;

            case 'approved_in_eur_and_amount_is_greater_than_500':
                return $transaction->currency === 'EUR' && $transaction->getAmount() > 500 ? Transaction::APPROVED : Transaction::DECLINED;

            case 'declined_if_amount_ends_in_7':
                return str_ends_with((string)$transaction->getAmount(), '7') ? Transaction::DECLINED : Transaction::APPROVED;

            case 'approved_if_amount_is_less_than_or_equals_20':
                return $transaction->getAmount() <= 20 ? Transaction::APPROVED : Transaction::DECLINED;

            case 'declined_if_currency_in_usd':
                return $transaction->currency === 'USD' ? Transaction::DECLINED : Transaction::APPROVED;

            case 'approved_if_meta_contains_valid_key':
                return isset($transaction->metadata['valid']) ? Transaction::APPROVED : Transaction::DECLINED;

            case 'declined_if_transaction_after_8pm':
                return now()->parse($transaction->created_at)->hour >= 20 ? Transaction::DECLINED : Transaction::APPROVED;

            case 'approved_only_if_currency_in_GBP_AUD':
                return in_array($transaction->currency, ['GBP', 'AUD']) ? Transaction::APPROVED : Transaction::DECLINED;

            case 'declined_if_email_contains_test':
                return str_contains($transaction->customer_email, 'test') ? Transaction::DECLINED : Transaction::APPROVED;

            case 'declined_if_divisible_by_3':
                return $transaction->getAmount() % 3 === 0 ? Transaction::DECLINED : Transaction::APPROVED;

            case 'always_declined':
            default:
                return Transaction::DECLINED;
        }
    }

    public static function isPrime($num)
    {
        if ($num <= 1) return false;
        for ($i = 2; $i <= sqrt($num); $i++) {
            if ($num % $i === 0) return false;
        }
        return true;
    }
}
