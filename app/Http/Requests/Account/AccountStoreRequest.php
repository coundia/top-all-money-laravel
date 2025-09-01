<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class AccountStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'code'        => ['nullable','string','max:255'],
            'currency'    => ['nullable','string','max:10'],
            'description' => ['nullable','string'],
            'isDefault'   => ['nullable','boolean'],
            'status'      => ['nullable','string','max:255'],
        ];
    }
}
