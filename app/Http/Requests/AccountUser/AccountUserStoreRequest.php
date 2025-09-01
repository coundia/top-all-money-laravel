<?php
// This request validates payload when creating an account user.

namespace App\Http\Requests\AccountUser;

use Illuminate\Foundation\Http\FormRequest;

class AccountUserStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'email'    => ['required','email'],
            'account'  => ['nullable','string','max:255'],
            'user'     => ['nullable','string','max:255'],
            'role'     => ['nullable','string','max:255'],
            'phone'    => ['nullable','string','max:255'],
            'status'   => ['nullable','string','max:255'],
        ];
    }
}
