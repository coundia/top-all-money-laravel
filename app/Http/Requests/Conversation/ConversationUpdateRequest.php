<?php

namespace App\Http\Requests\Conversation;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates update of a conversation.
 */
class ConversationUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title' => ['sometimes','string','max:255'],
            'createdBy' => ['sometimes','nullable','string','max:255'],
        ];
    }
}
