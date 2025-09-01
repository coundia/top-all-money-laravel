<?php

namespace App\Http\Requests\Message;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request validation for importing messages from CSV
 */
class MessageImportRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'file' => ['required','file','mimes:csv,txt','max:10240'],
        ];
    }
}
