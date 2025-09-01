<?php
// This request validates the bulk upsert payload for products.

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductBulkUpsertRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'items'                 => ['required','array','min:1'],
            'items.*.id'            => ['nullable','string'],
            'items.*.type'          => ['nullable','string', Rule::in(['CREATE','UPDATE','DELETE'])],
            'items.*.code'          => ['nullable','string','max:255'],
            'items.*.name'          => ['nullable','string','max:255'],
            'items.*.description'   => ['nullable','string'],
            'items.*.barcode'       => ['nullable','string','max:255'],
            'items.*.unitId'        => ['nullable','string','max:255'],
            'items.*.categoryId'    => ['nullable','string','max:255'],
            'items.*.defaultPrice'  => ['nullable','integer'],
            'items.*.purchasePrice' => ['nullable','integer'],
            'items.*.account'       => ['nullable','string','max:255'],
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
                    if (empty($row['code'])) $validator->errors()->add("items.$i.code", "code is required for CREATE.");
                    if (empty($row['name'])) $validator->errors()->add("items.$i.name", "name is required for CREATE.");
                }
            }
        });
    }
}
