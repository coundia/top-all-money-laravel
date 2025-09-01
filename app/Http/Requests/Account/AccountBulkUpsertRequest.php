<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccountBulkUpsertRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'items'               => ['required','array','min:1'],
            'items.*.id'          => ['nullable','string'],
            'items.*.type'        => ['nullable','string', Rule::in(['CREATE','UPDATE','DELETE'])],
            'items.*.code'        => ['nullable','string','max:255'],
            'items.*.currency'    => ['nullable','string','max:10'],
            'items.*.description' => ['nullable','string'],
            'items.*.isDefault'   => ['nullable','boolean'],
            'items.*.status'      => ['nullable','string','max:255'],
            // Tu peux ajouter ici d'autres colonnes si tu veux les valider strictement
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $items = $this->input('items', []);
            foreach ($items as $i => $row) {
                $type = strtoupper($row['type'] ?? 'CREATE'); // défaut = CREATE
                if (($type === 'DELETE' || $type === 'UPDATE') && empty($row['id'])) {
                    $validator->errors()->add("items.$i.id", "The id field is required when type is $type.");
                }
            }
        });
    }
}
