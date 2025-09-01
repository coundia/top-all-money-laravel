<?php

namespace App\Http\Requests\Conversation;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates CSV import for conversations.
 */
class ConversationImportRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'file' => ['required','file','mimes:csv,txt','max:10240'],
        ];
    }
}
