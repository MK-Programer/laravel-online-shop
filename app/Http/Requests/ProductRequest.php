<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
        return [
            'title' => 'required|string|unique:products,title',
            'slug' => 'required|string|unique:products,slug',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'compare_price' => 'numeric|nullable',
            'category' => 'required|integer',
            'sub_category' => 'nullable|integer',
            'brand' => 'nullable|integer',
            'is_featured' => 'required|in:Yes,No',
            'sku' => 'required|string|unique:products,sku',
            'bar_code' => 'nullable|string|unique:products,bar_code',
            'track_qty' => 'required|in:Yes,No',
            'qty' => 'required_if:track_qty,Yes|integer|nullable',
            'status' => 'required|boolean',
        ];
    }
}
