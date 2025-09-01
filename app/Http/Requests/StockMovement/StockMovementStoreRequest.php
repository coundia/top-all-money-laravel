<?php
// This request validates payload when creating a stock movement.

namespace App\Http\Requests\StockMovement;

use Illuminate\Foundation\Http\FormRequest;

class StockMovementStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'type_stock_movement' => ['required','string','max:255'],
            'quantity'            => ['required','integer'],
            'companyId'           => ['required','string'],
            'productVariantId'    => ['required','string'],
            'code'                => ['nullable','string','max:255'],
            'account'             => ['nullable','string','max:255'],
        ];
    }
}
