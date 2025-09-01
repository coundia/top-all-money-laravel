<?php
// This request validates the CSV file for product import.

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class ProductImportRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return ['file' => ['required','file','mimes:csv,txt','max:10240']];
    }
}
