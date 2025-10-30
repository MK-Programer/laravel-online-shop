<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $record = $this->route('record');

        return [
            'category' => 'required|integer|exists:categories,id',
            'name' => 'required|string|unique:sub_categories,name' . ($record ? ',' . $record : ''),
            'slug' => 'required|string|unique:sub_categories,slug' . ($record ? ',' . $record : ''),
            'status' => 'boolean',
        ];
    }
}
