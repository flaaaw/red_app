<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'amount' => 'sometimes|numeric',
            'date' => 'sometimes|date',
            'type' => 'sometimes|in:income,expense',
            'category' => 'sometimes|string|max:255',
            'color' => 'sometimes|string|max:7',
        ];
    }
}
