<?php
// This request validates payload when updating a debt.

namespace App\Http\Requests\Debt;

use Illuminate\Foundation\Http\FormRequest;

class DebtUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'code'        => ['sometimes','string','max:255'],
            'notes'       => ['sometimes','nullable','string'],
            'balance'     => ['sometimes','integer'],
            'balanceDebt' => ['sometimes','integer'],
            'dueDate'     => ['sometimes','nullable','date'],
            'account'     => ['sometimes','nullable','string','max:255'],
            'customerId'  => ['sometimes','nullable','string'],
        ];
    }
}
