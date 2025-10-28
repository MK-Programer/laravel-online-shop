<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
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
        //* Will be included in edit only
        $record = $this->route('record');

        return [
            'name' => 'required|unique:categories,name' . ($record ? ',' . $record : ''),
            'slug' => 'required|unique:categories,slug' . ($record ? ',' . $record : ''),
        ];
    }
}
