<?php
// This request validates payload when creating a product.

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'code'          => ['required','string','max:255'],
            'name'          => ['required','string','max:255'],
            'description'   => ['nullable','string'],
            'barcode'       => ['nullable','string','max:255'],
            'unitId'        => ['nullable','string','max:255'],
            'categoryId'    => ['nullable','string','max:255'],
            'defaultPrice'  => ['nullable','integer'],
            'purchasePrice' => ['nullable','integer'],
            'account'       => ['nullable','string','max:255'],
        ];
    }
}
