<?php

namespace App\Http\Requests\Conversation;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates creation of a conversation.
 */
class ConversationStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title' => ['required','string','max:255'],
            'createdBy' => ['sometimes','nullable','string','max:255'],
        ];
    }
}
