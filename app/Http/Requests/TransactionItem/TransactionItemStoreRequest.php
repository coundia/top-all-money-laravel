<?php

namespace App\Http\Requests\TransactionItem;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request validation for creating a transaction item.
 */
class TransactionItemStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'transactionId' => ['nullable','string'],
            'productId'     => ['nullable','string'],
            'label'         => ['required','string','max:255'],
            'quantity'      => ['nullable','integer','min:0'],
            'unitId'        => ['nullable','string'],
            'unitPrice'     => ['nullable','integer','min:0'],
            'total'         => ['nullable','integer','min:0'],
            'notes'         => ['nullable','string'],
            'code'          => ['nullable','string','max:255'],
            'account'       => ['nullable','string','max:255'],
        ];
    }
}
