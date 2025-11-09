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
        $record = $this->route('record');
        return [
            'title' => 'required|string|unique:products,title' . ($record ? ',' . $record : ''),
            'slug' => 'required|string|unique:products,slug' . ($record ? ',' . $record : ''),
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'shipping_returns' => 'nullable|string',
            'related_products' => 'nullable|string',
            'price' => 'required|numeric',
            'compare_price' => 'numeric|nullable',
            'category' => 'required|integer',
            'sub_category' => 'nullable|integer',
            'brand' => 'nullable|integer',
            'is_featured' => 'required|in:Yes,No',
            'sku' => 'required|string|unique:products,sku' . ($record ? ',' . $record : ''),
            'bar_code' => 'nullable|string|unique:products,bar_code' . ($record ? ',' . $record : ''),
            'track_qty' => 'required|in:Yes,No',
            'qty' => 'required_if:track_qty,Yes|integer|nullable',
            'status' => 'required|boolean',
            'images_order' => 'required_with:images_id|array|min:1',
            'images_order.*' => 'required|integer|min:1|distinct',
        ];
    }
}
