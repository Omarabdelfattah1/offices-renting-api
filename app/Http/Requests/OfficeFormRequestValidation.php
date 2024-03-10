<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OfficeFormRequestValidation extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $office = $this->route('office');
        $user = auth()->user();
        return is_null($office)? !is_null($user): $office->user_id == $user->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'title' =>[ 'required','string','max:191'],
            'description' =>[ 'required','string'],
            'lat' =>[ 'required','numeric'],
            'lng' =>[ 'required','numeric'],
            'address_line1' =>[ 'required','string','max:191'],
            'address_line2' =>[ 'nullable','string','max:191'],
            'price_per_day' =>[ 'required','numeric'],
            'monthly_discount' =>[ 'required','numeric'],
            'tags'=>[ 'array'],
            'featured_image_id' => 'nullable|integer|exists:images,id',
            'tags.*'=>[ 'required','integer'],
            'images'=>[ 'array'],
            'images.*'=>[ 'required','image'],
        ];
        if( $this->route('office') ) {
            foreach( $rules as $key => $value ) {
                if( in_array( 'required',$value )){
                    array_unshift($rules[$key],'sometimes');
                }
            }
        }
        return $rules;
    }
}
