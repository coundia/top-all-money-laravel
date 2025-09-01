<?php

namespace App\Http\Requests\Message;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request validation for creating a message
 */
class MessageStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'conversation_id' => ['required','string'],
            'content' => ['required','string'],
            'sender' => ['nullable','string'],
        ];
    }
}
