<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *   schema="CategoryCreateRequest",
 *   type="object",
 *   @OA\Property(property="code", type="string", nullable=true, maxLength=255),
 *   @OA\Property(property="description", type="string", nullable=true),
 *   @OA\Property(property="typeEntry", type="string", enum={"DEBIT","CREDIT"}, nullable=true),
 *   @OA\Property(property="account", type="string", nullable=true)
 * )
 */
class CategoryStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'code'        => ['nullable','string','max:255'],
            'description' => ['nullable','string'],
            'typeEntry'   => ['nullable','string','in:DEBIT,CREDIT'],
            'account'     => ['nullable','string'],
        ];
    }
}
