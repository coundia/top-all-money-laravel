<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class AccountUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'code'        => ['sometimes','nullable','string','max:255'],
            'currency'    => ['sometimes','nullable','string','max:10'],
            'description' => ['sometimes','nullable','string'],
            'isDefault'   => ['sometimes','boolean'],
            'status'      => ['sometimes','nullable','string','max:255'],
        ];
    }
}
