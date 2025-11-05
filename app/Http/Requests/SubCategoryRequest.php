<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $categoryId = $this->input('category');

        return [
            'category' => 'required|integer|exists:categories,id',
            'name' => [
                'required',
                'string',
                Rule::unique('sub_categories', 'name')
                    ->where(fn($query) => $query->where('category_id', $categoryId))
                    ->ignore($record),
            ],
            'slug' => [
                'required',
                'string',
                Rule::unique('sub_categories', 'slug')
                    ->where(fn($query) => $query->where('category_id', $categoryId))
                    ->ignore($record),
            ],
            'status' => 'boolean',
            'show_in_home' => 'boolean',
        ];
    }
}
