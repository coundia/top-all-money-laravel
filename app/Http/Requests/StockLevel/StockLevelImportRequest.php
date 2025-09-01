<?php

namespace App\Http\Requests\StockLevel;

use Illuminate\Foundation\Http\FormRequest;

class StockLevelImportRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'file'  => ['sometimes','file','mimes:csv,txt'],
            'items' => ['sometimes','array'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            if (!$this->hasFile('file') && !$this->filled('items')) {
                $v->errors()->add('file', 'Provide a CSV file or an items array.');
            }
        });
    }
}
