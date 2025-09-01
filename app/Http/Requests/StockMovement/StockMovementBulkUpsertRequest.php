<?php
// This request validates the bulk upsert payload for stock movements.

namespace App\Http\Requests\StockMovement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockMovementBulkUpsertRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'items'                     => ['required','array','min:1'],
            'items.*.id'                => ['nullable','string'],
            'items.*.type'              => ['nullable','string', Rule::in(['CREATE','UPDATE','DELETE'])],
            'items.*.type_stock_movement'=> ['nullable','string','max:255'],
            'items.*.quantity'          => ['nullable','integer'],
            'items.*.companyId'         => ['nullable','string'],
            'items.*.productVariantId'  => ['nullable','string'],
            'items.*.code'              => ['nullable','string','max:255'],
            'items.*.account'           => ['nullable','string','max:255'],
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
                    foreach (['type_stock_movement','quantity','companyId','productVariantId'] as $req) {
                        if (!isset($row[$req]) || $row[$req]==='') $validator->errors()->add("items.$i.$req", "$req is required for CREATE.");
                    }
                }
            }
        });
    }
}
