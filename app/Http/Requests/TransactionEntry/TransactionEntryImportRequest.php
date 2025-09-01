<?php
// This request validates the CSV file for transaction entry import.

namespace App\Http\Requests\TransactionEntry;

use Illuminate\Foundation\Http\FormRequest;

class TransactionEntryImportRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return ['file' => ['required','file','mimes:csv,txt','max:10240']];
    }
}
