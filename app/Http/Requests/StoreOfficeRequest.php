<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOfficeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:191',
            'description' => 'required|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'address_line1' => 'required|string|max:191',
            'address_line2' => 'nullable|string|max:191',
            'price_per_day' => 'required|numeric',
            'monthly_discount' => 'required|numeric',
            'tags'=> 'array',
            'tags.*'=> 'required|integer',
            'images'=> 'array',
            'images.*'=> 'required|image',
        ];
    }
}
