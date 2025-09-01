<?php
// This request validates the bulk upsert payload for debts.

namespace App\Http\Requests\Debt;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DebtBulkUpsertRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'items'                => ['required','array','min:1'],
            'items.*.id'           => ['nullable','string'],
            'items.*.type'         => ['nullable','string', Rule::in(['CREATE','UPDATE','DELETE'])],
            'items.*.code'         => ['nullable','string','max:255'],
            'items.*.notes'        => ['nullable','string'],
            'items.*.balance'      => ['nullable','integer'],
            'items.*.balanceDebt'  => ['nullable','integer'],
            'items.*.dueDate'      => ['nullable','date'],
            'items.*.account'      => ['nullable','string','max:255'],
            'items.*.customerId'   => ['nullable','string'],
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
                if ($t === 'CREATE' && empty($row['code'])) {
                    $validator->errors()->add("items.$i.code", "code is required for CREATE.");
                }
            }
        });
    }
}
