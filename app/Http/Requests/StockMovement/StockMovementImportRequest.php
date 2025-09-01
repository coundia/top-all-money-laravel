<?php
// This request validates the CSV file for stock movement import.

namespace App\Http\Requests\StockMovement;

use Illuminate\Foundation\Http\FormRequest;

class StockMovementImportRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['file'=>['required','file','mimes:csv,txt','max:10240']]; }
}
