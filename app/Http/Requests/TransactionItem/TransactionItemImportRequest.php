<?php

namespace App\Http\Requests\TransactionItem;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request validation for importing transaction items from CSV.
 */
class TransactionItemImportRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'file' => ['required','file','mimes:csv,txt','max:10240'],
        ];
    }
}
