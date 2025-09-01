<?php

namespace App\Http\Requests\TransactionItem;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request validation for updating a transaction item.
 */
class TransactionItemUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'transactionId' => ['sometimes','nullable','string'],
            'productId'     => ['sometimes','nullable','string'],
            'label'         => ['sometimes','string','max:255'],
            'quantity'      => ['sometimes','nullable','integer','min:0'],
            'unitId'        => ['sometimes','nullable','string'],
            'unitPrice'     => ['sometimes','nullable','integer','min:0'],
            'total'         => ['sometimes','nullable','integer','min:0'],
            'notes'         => ['sometimes','nullable','string'],
            'code'          => ['sometimes','nullable','string','max:255'],
            'account'       => ['sometimes','nullable','string','max:255'],
            'isDirty'       => ['sometimes','boolean'],
            'version'       => ['sometimes','nullable','integer','min:0'],
        ];
    }
}
