<?php

namespace App\Http\Requests\StockLevel;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Product;
use App\Models\Company;

class StockLevelUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $productTable = (new Product())->getTable();
        $companyTable = (new Company())->getTable();

        return [
            'code'             => ['sometimes','nullable','string','max:255'],
            'remoteId'         => ['sometimes','nullable','string','max:255'],
            'localId'          => ['sometimes','nullable','string','max:255'],
            'productVariantId' => ['sometimes','uuid',"exists:{$productTable},id"],
            'companyId'        => ['sometimes','uuid',"exists:{$companyTable},id"],
            'stockOnHand'      => ['sometimes','nullable','integer'],
            'stockAllocated'   => ['sometimes','nullable','integer'],
            'version'          => ['sometimes','nullable','integer'],
            'account'          => ['sometimes','nullable','string','max:255'],
            'isDirty'          => ['sometimes','boolean'],
            'createdBy'        => ['sometimes','nullable','uuid'],
            'syncAt'           => ['sometimes','nullable','date'],
            'deletedAt'        => ['sometimes','nullable','date'],
            'createdAt'        => ['sometimes','nullable','date'],
            'updatedAt'        => ['sometimes','nullable','date'],
        ];
    }
}
