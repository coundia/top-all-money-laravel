<?php
// This request validates payload when updating a stock movement.

namespace App\Http\Requests\StockMovement;

use Illuminate\Foundation\Http\FormRequest;

class StockMovementUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'type_stock_movement' => ['sometimes','string','max:255'],
            'quantity'            => ['sometimes','integer'],
            'companyId'           => ['sometimes','string'],
            'productVariantId'    => ['sometimes','string'],
            'code'                => ['sometimes','nullable','string','max:255'],
            'account'             => ['sometimes','nullable','string','max:255'],
        ];
    }
}
