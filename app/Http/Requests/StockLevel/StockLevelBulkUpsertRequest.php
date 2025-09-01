<?php

namespace App\Http\Requests\StockLevel;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Product;
use App\Models\Company;

class StockLevelBulkUpsertRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        if ($this->has('items') && is_array($this->items)) {
            $items = collect($this->input('items'))
                ->map(function ($row) {
                    if (isset($row['type'])) {
                        $row['type'] = strtolower($row['type']);
                    }
                    return $row;
                })->all();
            $this->merge(['items' => $items]);
        }
    }

    public function rules(): array
    {
        $productTable = (new Product())->getTable();
        $companyTable = (new Company())->getTable();

        return [
            'items'                       => ['required','array','min:1'],
            'items.*.id'                  => ['nullable','uuid'],
            'items.*.type'                => ['required','in:create,update,upsert,delete,restore'],
            'items.*.productVariantId'    => ['required_unless:items.*.type,delete,restore','uuid',"exists:{$productTable},id"],
            'items.*.companyId'           => ['required_unless:items.*.type,delete,restore','uuid',"exists:{ $companyTable },id"],
            'items.*.code'                => ['sometimes','nullable','string','max:255'],
            'items.*.remoteId'            => ['sometimes','nullable','string','max:255'],
            'items.*.localId'             => ['sometimes','nullable','string','max:255'],
            'items.*.stockOnHand'         => ['sometimes','nullable','integer'],
            'items.*.stockAllocated'      => ['sometimes','nullable','integer'],
            'items.*.version'             => ['sometimes','nullable','integer'],
            'items.*.account'             => ['sometimes','nullable','string','max:255'],
            'items.*.isDirty'             => ['sometimes','boolean'],
            'items.*.createdBy'           => ['sometimes','nullable','uuid'],
            'items.*.syncAt'              => ['sometimes','nullable','date'],
            'items.*.deletedAt'           => ['sometimes','nullable','date'],
            'items.*.createdAt'           => ['sometimes','nullable','date'],
            'items.*.updatedAt'           => ['sometimes','nullable','date'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.*.type.in' => 'Type must be one of: create, update, upsert, delete, restore.',
        ];
    }
}
