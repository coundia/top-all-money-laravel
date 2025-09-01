<?php
// This request validates payload when updating a transaction entry.

namespace App\Http\Requests\TransactionEntry;

use Illuminate\Foundation\Http\FormRequest;

class TransactionEntryUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'code'            => ['sometimes','nullable','string','max:255'],
            'description'     => ['sometimes','nullable','string'],
            'amount'          => ['sometimes','integer'],
            'typeEntry'       => ['sometimes','string','in:DEBIT,CREDIT'],
            'dateTransaction' => ['sometimes','nullable','date'],
            'status'          => ['sometimes','nullable','string','max:255'],
            'entityName'      => ['sometimes','nullable','string','max:255'],
            'entityId'        => ['sometimes','nullable','string','max:255'],
            'accountId'       => ['sometimes','nullable','string'],
            'categoryId'      => ['sometimes','nullable','string'],
            'companyId'       => ['sometimes','nullable','string'],
            'customerId'      => ['sometimes','nullable','string'],
            'debtId'          => ['sometimes','nullable','string'],
        ];
    }
}
