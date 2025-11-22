<?php

namespace App\Http\Requests;

use App\Models\Country;
use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    /**
     * based on country selected validate mobile **
     */
    public ?Country $country = null;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * This runs BEFORE validation.
     */
    protected function prepareForValidation()
    {
        $this->country = Country::find($this->input('country'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|min:5',
            'last_name' => 'required|string',
            'email' => 'required|email',
            'country' => 'required|exists:countries,id',
            'address' =>  'required|string|min:20',
            'apartment' => 'nullable|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'zip' => 'required|integer',
            'mobile' => 'required|phone:' . $this->country?->code,
            'order_notes' => 'nullable|string'
        ];
    }

    public function messages()
    {
        return [
            'mobile.phone' => 'Invalid mobile number for country: ' . $this->country?->name,
        ];
    }
}
