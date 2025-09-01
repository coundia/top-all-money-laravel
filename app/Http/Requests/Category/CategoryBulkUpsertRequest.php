<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *   schema="CategoryBulkUpsertRequest",
 *   type="object",
 *   required={"items"},
 *   @OA\Property(
 *     property="items",
 *     type="array",
 *     @OA\Items(
 *       allOf={
 *         @OA\Schema(ref="#/components/schemas/CategoryUpdateRequest"),
 *         @OA\Schema(
 *           @OA\Property(property="id", type="string", format="uuid", nullable=true),
 *           @OA\Property(property="type", type="string", enum={"CREATE","UPDATE","DELETE"})
 *         )
 *       }
 *     )
 *   )
 * )
 */
class CategoryBulkUpsertRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'items'               => ['required','array','min:1'],
            'items.*.id'          => ['nullable','string'],
            'items.*.type'        => ['nullable','string', Rule::in(['CREATE','UPDATE','DELETE'])],
            'items.*.code'        => ['nullable','string','max:255'],
            'items.*.description' => ['nullable','string'],
            'items.*.typeEntry'   => ['nullable','string', Rule::in(['DEBIT','CREDIT'])],
            'items.*.account'     => ['nullable','string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $items = $this->input('items', []);
            foreach ($items as $i => $row) {
                $t = strtoupper($row['type'] ?? 'CREATE');
                if (in_array($t, ['UPDATE','DELETE'], true) && empty($row['id'])) {
                    $validator->errors()->add("items.$i.id", "The id field is required when type is $t.");
                }
            }
        });
    }
}
