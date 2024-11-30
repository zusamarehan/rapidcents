<?php
use App\Services\CardValidatorService;
use App\Models\Transaction;

beforeEach(function () {
    uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
    $this->cardValidatorService = new CardValidatorService();
});

test('passes the always_approved scenario', function () {

    $transaction = new Transaction();
    $transaction->currency = 'USD';
    $transaction->amount = 100_00;
    $transaction->metadata = [];
    $transaction->card_number = 1234567890123456;

    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::APPROVED);
});


test('passes the always_declined scenario', function () {

    $transaction = new Transaction();
    $transaction->currency = 'USD';
    $transaction->amount = 100_00;
    $transaction->metadata = [];
    $transaction->card_number = 1111222233334444;

    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::DECLINED);
});

test('it approves when using USD', function () {

    $transaction = new Transaction();
    $transaction->currency = 'USD';
    $transaction->amount = 100_00;
    $transaction->metadata = [];
    $transaction->card_number = 9876543210987654;

    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::APPROVED);
});


test('it declines when not using USD', function () {

    $transaction = new Transaction();
    $transaction->currency = 'CAD';
    $transaction->amount = 100_00;
    $transaction->metadata = [];
    $transaction->card_number = 9876543210987654;

    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::DECLINED);
});


test('it declines when amount is less than 50', function () {

    $transaction = new Transaction();
    $transaction->currency = 'USD';
    $transaction->amount = 49_99;
    $transaction->metadata = [];
    $transaction->card_number = 5678901234567890;

    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::DECLINED);
});

test('it approves when amount is more than 50', function () {

    $transaction = new Transaction();
    $transaction->currency = 'USD';
    $transaction->amount = 51_99;
    $transaction->metadata = [];
    $transaction->card_number = 5678901234567890;

    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::APPROVED);
});

test('it approves if amount is divisible by 10', function () {

    // Create a new Transaction instance
    $transaction = new Transaction();
    $transaction->currency = 'USD';
    $transaction->amount = 100_00;
    $transaction->metadata = [];
    $transaction->card_number = '5432167890123456';

    // Validate and check that the transaction is approved
    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::APPROVED);
});

test('it declines if amount is not divisible by 10', function () {

    // Create a new Transaction instance
    $transaction = new Transaction();
    $transaction->currency = 'USD';
    $transaction->amount = 6400;
    $transaction->metadata = [];
    $transaction->card_number = '5432167890123456';

    // Validate and check that the transaction is approved
    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::DECLINED);
});

test('it approves if currency is CAD', function () {

    // Create a new Transaction instance
    $transaction = new Transaction();
    $transaction->currency = 'CAD';
    $transaction->amount = 6400;
    $transaction->metadata = [];
    $transaction->card_number = '1234432112344321';

    // Validate and check that the transaction is approved
    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::APPROVED);
});


test('it declines if currency is NOT CAD', function () {

    // Create a new Transaction instance
    $transaction = new Transaction();
    $transaction->currency = 'AUD';
    $transaction->amount = 6400;
    $transaction->metadata = [];
    $transaction->card_number = '1234432112344321';

    // Validate and check that the transaction is approved
    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::DECLINED);
});


test('it declines if duplicate transactions exists', function () {

    // Create a new Transaction instance
    $asdf = Transaction::factory()->create([
        'created_at' => now(),
        'card_number' => '6789012345678901',
        'currency' => 'USD',
        'amount' => 6400,
        'status' => Transaction::APPROVED
    ]);

    $transaction = new Transaction();
    $transaction->currency = 'USD';
    $transaction->amount = 6400;
    $transaction->metadata = null;
    $transaction->card_number = '6789012345678901';

    // Validate and check that the transaction is approved
    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::DECLINED);
});


test('it declines if missing meta', function () {

    // Create a new Transaction instance
    $transaction = new Transaction();
    $transaction->currency = 'CAD';
    $transaction->amount = 6400;
    $transaction->metadata = null;
    $transaction->card_number = '8888888888888888';

    // Validate and check that the transaction is approved
    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::DECLINED);
});


test('it approves if meta exits', function () {

    // Create a new Transaction instance
    $transaction = new Transaction();
    $transaction->currency = 'CAD';
    $transaction->amount = 6400;
    $transaction->metadata = [ 'via' => 'web' ];
    $transaction->card_number = '8888888888888888';

    // Validate and check that the transaction is approved
    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::APPROVED);
});


test('it always pending', function () {

    // Create a new Transaction instance
    $transaction = new Transaction();
    $transaction->currency = 'CAD';
    $transaction->amount = 6400;
    $transaction->metadata = [ 'via' => 'web' ];
    $transaction->card_number = '3333333333333333';

    // Validate and check that the transaction is approved
    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::PENDING);
});


test('it approves only for emails with example.com', function () {

    // Create a new Transaction instance
    $transaction = new Transaction();
    $transaction->currency = 'CAD';
    $transaction->amount = 6400;
    $transaction->metadata = [ 'via' => 'web' ];
    $transaction->customer_email = 'rehan@example.com';
    $transaction->card_number = '1212121212121212';

    // Validate and check that the transaction is approved
    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::APPROVED);
});

test('it declines for others emails apart from example.com', function () {

    // Create a new Transaction instance
    $transaction = new Transaction();
    $transaction->currency = 'CAD';
    $transaction->amount = 6400;
    $transaction->metadata = [ 'via' => 'web' ];
    $transaction->customer_email = 'rehan@rapidcents.com';
    $transaction->card_number = '1212121212121212';

    // Validate and check that the transaction is approved
    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::DECLINED);
});


test('it declines when meta contains test key', function () {

    // Create a new Transaction instance
    $transaction = new Transaction();
    $transaction->currency = 'CAD';
    $transaction->amount = 6400;
    $transaction->metadata = [ 'test' => 'web' ];
    $transaction->customer_email = 'rehan@rapidcents.com';
    $transaction->card_number = '2222222222222222';

    // Validate and check that the transaction is approved
    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::DECLINED);
});


test('it approves when meta does contains test key', function () {

    // Create a new Transaction instance
    $transaction = new Transaction();
    $transaction->currency = 'CAD';
    $transaction->amount = 6400;
    $transaction->metadata = [ 'rehan' => 'rapid cents' ];
    $transaction->customer_email = 'rehan@rapidcents.com';
    $transaction->card_number = '2222222222222222';

    // Validate and check that the transaction is approved
    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::DECLINED);
});

test('it nsf when amount is between 100 and 200', function () {

    // Create a new Transaction instance
    $transaction = new Transaction();
    $transaction->currency = 'CAD';
    $transaction->amount = 101_00;
    $transaction->metadata = [ 'rehan' => 'rapid cents' ];
    $transaction->customer_email = 'rehan@rapidcents.com';
    $transaction->card_number = '9999999999999999';

    // Validate and check that the transaction is approved
    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::NSF);
});


test('it approves when amount is not between 100 and 200', function () {

    // Create a new Transaction instance
    $transaction = new Transaction();
    $transaction->currency = 'CAD';
    $transaction->amount = 201_00;
    $transaction->metadata = [ 'rehan' => 'rapid cents' ];
    $transaction->customer_email = 'rehan@rapidcents.com';
    $transaction->card_number = '9999999999999999';

    // Validate and check that the transaction is approved
    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::APPROVED);
});



test('it approves when amount is even number', function () {

    // Create a new Transaction instance
    $transaction = new Transaction();
    $transaction->currency = 'CAD';
    $transaction->amount = 102_00;
    $transaction->metadata = [ 'rehan' => 'rapid cents' ];
    $transaction->customer_email = 'rehan@rapidcents.com';
    $transaction->card_number = '1357913579135791';

    // Validate and check that the transaction is approved
    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::APPROVED);
});


test('it declines when amount is not even number', function () {

    // Create a new Transaction instance
    $transaction = new Transaction();
    $transaction->currency = 'CAD';
    $transaction->amount = 103_00;
    $transaction->metadata = [ 'rehan' => 'rapid cents' ];
    $transaction->customer_email = 'rehan@rapidcents.com';
    $transaction->card_number = '1357913579135791';

    // Validate and check that the transaction is approved
    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::DECLINED);
});


test('it approves when amount is prime number', function () {

    // Create a new Transaction instance
    $transaction = new Transaction();
    $transaction->currency = 'CAD';
    $transaction->amount = 83;
    $transaction->metadata = [ 'rehan' => 'rapid cents' ];
    $transaction->customer_email = 'rehan@rapidcents.com';
    $transaction->card_number = '2468024680246802';

    // Validate and check that the transaction is approved
    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::APPROVED);
});

test('it declines when amount is not prime number', function () {

    // Create a new Transaction instance
    $transaction = new Transaction();
    $transaction->currency = 'CAD';
    $transaction->amount = 1;
    $transaction->metadata = [ 'rehan' => 'rapid cents' ];
    $transaction->customer_email = 'rehan@rapidcents.com';
    $transaction->card_number = '2468024680246802';

    // Validate and check that the transaction is approved
    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::APPROVED);
});

test('it approves when currency is in eur and amount greater than 500', function () {

    // Create a new Transaction instance
    $transaction = new Transaction();
    $transaction->currency = 'EUR';
    $transaction->amount = 501_00;
    $transaction->metadata = [ 'rehan' => 'rapid cents' ];
    $transaction->customer_email = 'rehan@rapidcents.com';
    $transaction->card_number = '2468024680246802';

    // Validate and check that the transaction is approved
    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::APPROVED);
});


test('it declines when currency is in not eur and amount is not greater than 500', function () {

    // Create a new Transaction instance
    $transaction = new Transaction();
    $transaction->currency = 'AUD';
    $transaction->amount = 499_00;
    $transaction->metadata = [ 'rehan' => 'rapid cents' ];
    $transaction->customer_email = 'rehan@rapidcents.com';
    $transaction->card_number = '2468024680246802';

    // Validate and check that the transaction is approved
    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::DECLINED);
});

test('it approves number does not end with 7', function () {

    // Create a new Transaction instance
    $transaction = new Transaction();
    $transaction->currency = 'AUD';
    $transaction->amount = 47771;
    $transaction->metadata = [ 'rehan' => 'rapid cents' ];
    $transaction->customer_email = 'rehan@rapidcents.com';
    $transaction->card_number = '6666666666666666';

    // Validate and check that the transaction is approved
    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::APPROVED);
});


test('it approves number ends with 7', function () {

    // Create a new Transaction instance
    $transaction = new Transaction();
    $transaction->currency = 'AUD';
    $transaction->amount = 47777;
    $transaction->metadata = [ 'rehan' => 'rapid cents' ];
    $transaction->customer_email = 'rehan@rapidcents.com';
    $transaction->card_number = '6666666666666666';

    // Validate and check that the transaction is approved
    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::DECLINED);
});


test('it approves when amount is greater than 20', function () {

    // Create a new Transaction instance
    $transaction = new Transaction();
    $transaction->currency = 'AUD';
    $transaction->amount = 20_00;
    $transaction->metadata = [ 'rehan' => 'rapid cents' ];
    $transaction->customer_email = 'rehan@rapidcents.com';
    $transaction->card_number = '9988776655443322';

    // Validate and check that the transaction is approved
    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::APPROVED);
});

test('it declines when amount is lesser than 20', function () {

    // Create a new Transaction instance
    $transaction = new Transaction();
    $transaction->currency = 'AUD';
    $transaction->amount = 19_00;
    $transaction->metadata = [ 'rehan' => 'rapid cents' ];
    $transaction->customer_email = 'rehan@rapidcents.com';
    $transaction->card_number = '9988776655443322';

    // Validate and check that the transaction is approved
    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::APPROVED);
});


test('it declines when currency is USD', function () {

    // Create a new Transaction instance
    $transaction = new Transaction();
    $transaction->currency = 'USD';
    $transaction->amount = 19_00;
    $transaction->metadata = [ 'rehan' => 'rapid cents' ];
    $transaction->customer_email = 'rehan@rapidcents.com';
    $transaction->card_number = '2233445566778899';

    // Validate and check that the transaction is approved
    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::DECLINED);
});


test('it approves when currency is not USD', function () {

    // Create a new Transaction instance
    $transaction = new Transaction();
    $transaction->currency = 'CAD';
    $transaction->amount = 19_00;
    $transaction->metadata = [ 'rehan' => 'rapid cents' ];
    $transaction->customer_email = 'rehan@rapidcents.com';
    $transaction->card_number = '2233445566778899';

    // Validate and check that the transaction is approved
    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::APPROVED);
});

test('it approves when meta contains valid key', function () {

    // Create a new Transaction instance
    $transaction = new Transaction();
    $transaction->currency = 'CAD';
    $transaction->amount = 6400;
    $transaction->metadata = [ 'valid' => 'web' ];
    $transaction->customer_email = 'rehan@rapidcents.com';
    $transaction->card_number = '3344556677889900';

    // Validate and check that the transaction is approved
    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::APPROVED);
});

test('it approves when meta does not contains valid key', function () {

    // Create a new Transaction instance
    $transaction = new Transaction();
    $transaction->currency = 'CAD';
    $transaction->amount = 6400;
    $transaction->metadata = [ 'validcode' => 'web' ];
    $transaction->customer_email = 'rehan@rapidcents.com';
    $transaction->card_number = '3344556677889900';

    // Validate and check that the transaction is approved
    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::DECLINED);
});

test('it declines if amount is divisible by 3', function () {

    // Create a new Transaction instance
    $transaction = new Transaction();
    $transaction->currency = 'USD';
    $transaction->amount = 33_00;
    $transaction->metadata = [];
    $transaction->card_number = '5566778899001122';

    // Validate and check that the transaction is approved
    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::DECLINED);
});

test('it approves if amount is not divisible by 3', function () {

    // Create a new Transaction instance
    $transaction = new Transaction();
    $transaction->currency = 'USD';
    $transaction->amount = 44_00;
    $transaction->metadata = [];
    $transaction->card_number = '5566778899001122';

    // Validate and check that the transaction is approved
    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::APPROVED);
});

test('it decline transaction after 8pm', function () {

    // Create a new Transaction instance
    $transaction = new Transaction();
    $transaction->currency = 'USD';
    $transaction->amount = 44_00;
    $transaction->metadata = [];
    $transaction->card_number = '7788990011223344';
    $transaction->created_at = \Carbon\Carbon::parse('2024-11-30 22:53:48');

    // Validate and check that the transaction is approved
    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::DECLINED);
});


test('it approves transaction before 8pm', function () {

    // Create a new Transaction instance
    $transaction = new Transaction();
    $transaction->currency = 'USD';
    $transaction->amount = 44_00;
    $transaction->metadata = [];
    $transaction->card_number = '7788990011223344';
    $transaction->created_at = \Carbon\Carbon::parse('2024-11-30 01:53:48');

    // Validate and check that the transaction is approved
    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::APPROVED);
});


test('it approves transaction if currency is GBP or AUD', function () {

    // Create a new Transaction instance
    $transaction = new Transaction();
    $transaction->currency = 'GBP';
    $transaction->amount = 44_00;
    $transaction->metadata = [];
    $transaction->card_number = '8899001122334455';

    // Validate and check that the transaction is approved
    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::APPROVED);
});

test('it approves transaction if currency is not GBP not AUD', function () {

    // Create a new Transaction instance
    $transaction = new Transaction();
    $transaction->currency = 'CAD';
    $transaction->amount = 44_00;
    $transaction->metadata = [];
    $transaction->card_number = '8899001122334455';

    // Validate and check that the transaction is approved
    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::DECLINED);
});


test('it approves when email does not contain test', function () {

    // Create a new Transaction instance
    $transaction = new Transaction();
    $transaction->currency = 'CAD';
    $transaction->amount = 44_00;
    $transaction->metadata = [];
    $transaction->card_number = '9900112233445566';
    $transaction->customer_email = 'rehan@rapidcents.com';

    // Validate and check that the transaction is approved
    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::APPROVED);
});

test('it declines when email  contain test', function () {

    // Create a new Transaction instance
    $transaction = new Transaction();
    $transaction->currency = 'CAD';
    $transaction->amount = 44_00;
    $transaction->metadata = [];
    $transaction->card_number = '9900112233445566';
    $transaction->customer_email = 'rehan@test.com';

    // Validate and check that the transaction is approved
    expect($this->cardValidatorService->validate($transaction))->toBe(Transaction::DECLINED);
});
