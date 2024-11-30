<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'card_number' => ['required', 'string'],
            'amount' => ['required', 'numeric'],
            'currency' => ['required', 'string', Rule::in(config('currencies'))],
            'customer_email' => ['required', 'string'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
