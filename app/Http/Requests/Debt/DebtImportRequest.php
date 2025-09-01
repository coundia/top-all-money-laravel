<?php
// This request validates the CSV file for debt import.

namespace App\Http\Requests\Debt;

use Illuminate\Foundation\Http\FormRequest;

class DebtImportRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['file'=>['required','file','mimes:csv,txt','max:10240']]; }
}
