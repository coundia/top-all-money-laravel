<?php

namespace App\Http\Requests\TransactionItem;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request validation for bulk upsert of transaction items.
 */
class TransactionItemBulkUpsertRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'items'                 => ['required','array','min:1'],
            'items.*.id'            => ['nullable','string'],
            'items.*.type'          => ['nullable','string', Rule::in(['CREATE','UPDATE','DELETE'])],
            'items.*.transactionId' => ['nullable','string'],
            'items.*.productId'     => ['nullable','string'],
            'items.*.label'         => ['nullable','string','max:255'],
            'items.*.quantity'      => ['nullable','integer','min:0'],
            'items.*.unitId'        => ['nullable','string'],
            'items.*.unitPrice'     => ['nullable','integer','min:0'],
            'items.*.total'         => ['nullable','integer','min:0'],
            'items.*.notes'         => ['nullable','string'],
            'items.*.code'          => ['nullable','string','max:255'],
            'items.*.account'       => ['nullable','string','max:255'],
            'items.*.isDirty'       => ['nullable','boolean'],
            'items.*.version'       => ['nullable','integer','min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $items = $this->input('items', []);
            foreach ($items as $i => $row) {
                $type = strtoupper($row['type'] ?? 'CREATE');
                if (in_array($type, ['UPDATE','DELETE'], true) && empty($row['id'])) {
                    $validator->errors()->add("items.$i.id", "The id field is required when type is $type.");
                }
            }
        });
    }
}
