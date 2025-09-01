<?php
// This request validates the CSV file for account user import.

namespace App\Http\Requests\AccountUser;

use Illuminate\Foundation\Http\FormRequest;

class AccountUserImportRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['file'=>['required','file','mimes:csv,txt','max:10240']]; }
}
