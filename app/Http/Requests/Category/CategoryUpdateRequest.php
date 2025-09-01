<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *   schema="CategoryUpdateRequest",
 *   type="object",
 *   @OA\Property(property="code", type="string", nullable=true, maxLength=255),
 *   @OA\Property(property="description", type="string", nullable=true),
 *   @OA\Property(property="typeEntry", type="string", enum={"DEBIT","CREDIT"}, nullable=true),
 *   @OA\Property(property="account", type="string", nullable=true),
 *   @OA\Property(property="isDirty", type="boolean", nullable=true),
 *   @OA\Property(property="status", type="string", nullable=true)
 * )
 */
class CategoryUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'code'        => ['sometimes','nullable','string','max:255'],
            'description' => ['sometimes','nullable','string'],
            'typeEntry'   => ['sometimes','nullable','string','in:DEBIT,CREDIT'],
            'account'     => ['sometimes','nullable','string'],
            'isDirty'     => ['sometimes','boolean'],
            'status'      => ['sometimes','nullable','string'],
        ];
    }
}
