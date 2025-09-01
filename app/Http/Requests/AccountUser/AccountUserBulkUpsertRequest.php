<?php
// This request validates the bulk upsert payload for account users.

namespace App\Http\Requests\AccountUser;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccountUserBulkUpsertRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'items'        => ['required','array','min:1'],
            'items.*.id'   => ['nullable','string'],
            'items.*.type' => ['nullable','string', Rule::in(['CREATE','UPDATE','DELETE'])],
            'items.*.email'=> ['nullable','email'],
            'items.*.role' => ['nullable','string','max:255'],
            'items.*.status'=>['nullable','string','max:255'],
        ];
    }
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ($this->input('items', []) as $i => $row) {
                $t = strtoupper($row['type'] ?? 'CREATE');
                if (in_array($t, ['UPDATE','DELETE'], true) && empty($row['id'])) {
                    $validator->errors()->add("items.$i.id", "The id field is required when type is $t.");
                }
                if ($t === 'CREATE' && empty($row['email'])) {
                    $validator->errors()->add("items.$i.email", "email is required for CREATE.");
                }
            }
        });
    }
}
