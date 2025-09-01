<?php

namespace App\Http\Requests\StockLevel;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Product;
use App\Models\Company;

class StockLevelStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $productTable = (new Product())->getTable();
        $companyTable = (new Company())->getTable();

        return [
            'id'               => ['nullable','uuid'],
            'code'             => ['nullable','string','max:255'],
            'remoteId'         => ['nullable','string','max:255'],
            'localId'          => ['nullable','string','max:255'],
            'productVariantId' => ['required','uuid',"exists:{$productTable},id"],
            'companyId'        => ['required','uuid',"exists:{$companyTable},id"],
            'stockOnHand'      => ['nullable','integer'],
            'stockAllocated'   => ['nullable','integer'],
            'version'          => ['nullable','integer'],
            'account'          => ['nullable','string','max:255'],
            'isDirty'          => ['nullable','boolean'],
            'createdBy'        => ['nullable','uuid'],
            'syncAt'           => ['nullable','date'],
            'deletedAt'        => ['nullable','date'],
            'createdAt'        => ['nullable','date'],
            'updatedAt'        => ['nullable','date'],
        ];
    }
}
