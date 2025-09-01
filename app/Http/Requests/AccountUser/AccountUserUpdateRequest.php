<?php
// This request validates payload when updating an account user.

namespace App\Http\Requests\AccountUser;

use Illuminate\Foundation\Http\FormRequest;

class AccountUserUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'email'  => ['sometimes','email'],
            'phone'  => ['sometimes','nullable','string','max:255'],
            'role'   => ['sometimes','nullable','string','max:255'],
            'status' => ['sometimes','nullable','string','max:255'],
        ];
    }
}
