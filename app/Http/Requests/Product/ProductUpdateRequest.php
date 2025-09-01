<?php
// This request validates payload when updating a product.

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class ProductUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'code'          => ['sometimes','string','max:255'],
            'name'          => ['sometimes','string','max:255'],
            'description'   => ['sometimes','nullable','string'],
            'barcode'       => ['sometimes','nullable','string','max:255'],
            'unitId'        => ['sometimes','nullable','string','max:255'],
            'categoryId'    => ['sometimes','nullable','string','max:255'],
            'defaultPrice'  => ['sometimes','integer'],
            'purchasePrice' => ['sometimes','integer'],
            'account'       => ['sometimes','nullable','string','max:255'],
        ];
    }
}
