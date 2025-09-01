<?php

namespace App\Http\Requests\Message;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request validation for updating a message
 */
class MessageUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'content' => ['sometimes','string'],
            'sender' => ['sometimes','nullable','string'],
        ];
    }
}
