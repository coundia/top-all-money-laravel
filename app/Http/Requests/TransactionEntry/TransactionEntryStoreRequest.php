<?php
// This request validates payload when creating a transaction entry.

namespace App\Http\Requests\TransactionEntry;

use Illuminate\Foundation\Http\FormRequest;

class TransactionEntryStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'code'            => ['nullable','string','max:255'],
            'description'     => ['nullable','string'],
            'amount'          => ['required','integer'],
            'typeEntry'       => ['required','string','in:DEBIT,CREDIT'],
            'dateTransaction' => ['nullable','date'],
            'status'          => ['nullable','string','max:255'],
            'entityName'      => ['nullable','string','max:255'],
            'entityId'        => ['nullable','string','max:255'],
            'accountId'       => ['nullable','string'],
            'categoryId'      => ['nullable','string'],
            'companyId'       => ['nullable','string'],
            'customerId'      => ['nullable','string'],
            'debtId'          => ['nullable','string'],
        ];
    }
}
