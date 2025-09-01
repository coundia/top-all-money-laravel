<?php
// This request validates the bulk upsert payload for transaction entries.

namespace App\Http\Requests\TransactionEntry;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionEntryBulkUpsertRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'items'                 => ['required','array','min:1'],
            'items.*.id'            => ['nullable','string'],
            'items.*.type'          => ['nullable','string', Rule::in(['CREATE','UPDATE','DELETE'])],
            'items.*.amount'        => ['nullable','integer'],
            'items.*.typeEntry'     => ['nullable','string', Rule::in(['DEBIT','CREDIT'])],
            'items.*.code'          => ['nullable','string','max:255'],
            'items.*.description'   => ['nullable','string'],
            'items.*.dateTransaction'=> ['nullable','date'],
            'items.*.status'        => ['nullable','string','max:255'],
            'items.*.entityName'    => ['nullable','string','max:255'],
            'items.*.entityId'      => ['nullable','string','max:255'],
            'items.*.accountId'     => ['nullable','string'],
            'items.*.categoryId'    => ['nullable','string'],
            'items.*.companyId'     => ['nullable','string'],
            'items.*.customerId'    => ['nullable','string'],
            'items.*.debtId'        => ['nullable','string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ($this->input('items', []) as $i => $row) {
                $t = strtoupper($row['type'] ?? 'CREATE');
                if (in_array($t, ['UPDATE','DELETE'], true) && empty($row['id'])) {
                    $validator->errors()->add("items.$i.id", "The id field is required when type is $t.");
                }
                if ($t === 'CREATE') {
                    if (!isset($row['amount'])) {
                        $validator->errors()->add("items.$i.amount", "amount is required for CREATE.");
                    }
                    if (empty($row['typeEntry'])) {
                        $validator->errors()->add("items.$i.typeEntry", "typeEntry is required for CREATE.");
                    }
                }
            }
        });
    }
}
