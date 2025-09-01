<?php

namespace App\Http\Requests\Message;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request validation for bulk upsert of messages
 */
class MessageBulkUpsertRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'items' => ['required','array','min:1'],
            'items.*.id' => ['nullable','string'],
            'items.*.type' => ['nullable','string', Rule::in(['CREATE','UPDATE','DELETE'])],
            'items.*.conversation_id' => ['required','string'],
            'items.*.content' => ['nullable','string'],
            'items.*.sender' => ['nullable','string'],
        ];
    }
}
