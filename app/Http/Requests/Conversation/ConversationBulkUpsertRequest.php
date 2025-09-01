<?php

namespace App\Http\Requests\Conversation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates bulk upsert payload for conversations.
 */
class ConversationBulkUpsertRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'items' => ['required','array','min:1'],
            'items.*.id' => ['nullable','string'],
            'items.*.type' => ['nullable','string', Rule::in(['CREATE','UPDATE','DELETE'])],
            'items.*.title' => ['nullable','string','max:255'],
            'items.*.createdBy' => ['nullable','string','max:255'],
            'items.*.created_at' => ['nullable','date'],
            'items.*.updated_at' => ['nullable','date'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $items = $this->input('items', []);
            foreach ($items as $i => $row) {
                $type = strtoupper($row['type'] ?? 'CREATE');
                if (in_array($type, ['UPDATE','DELETE'], true) && empty($row['id'])) {
                    $validator->errors()->add("items.$i.id", "The id field is required when type is $type.");
                }
            }
        });
    }
}
