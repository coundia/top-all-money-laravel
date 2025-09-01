<?php
// This request validates payload when creating a debt.

namespace App\Http\Requests\Debt;

use Illuminate\Foundation\Http\FormRequest;

class DebtStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'code'        => ['required','string','max:255'],
            'notes'       => ['nullable','string'],
            'balance'     => ['nullable','integer'],
            'balanceDebt' => ['nullable','integer'],
            'dueDate'     => ['nullable','date'],
            'account'     => ['nullable','string','max:255'],
            'customerId'  => ['nullable','string'],
        ];
    }
}
