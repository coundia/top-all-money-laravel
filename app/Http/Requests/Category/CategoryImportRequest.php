<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *   schema="CategoryImportRequest",
 *   type="object",
 *   required={"file"},
 *   @OA\Property(property="file", type="string", format="binary")
 * )
 */
class CategoryImportRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'file' => ['required','file','mimes:csv,txt','max:10240'],
        ];
    }
}
