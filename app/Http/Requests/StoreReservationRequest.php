<?php

namespace App\Http\Requests;

use App\Models\Office;
use App\Models\Reservation;
use App\Rules\IsOfficeAvaiable;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
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
        $rules = [
            'office_id' => ['required', 'integer', 'exists:offices,id', new IsOfficeAvaiable($this)],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ];
        return $rules;
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();
        $data['user_id'] = auth()->id();
        $count_days = Carbon::parse($data['end_date'])->diffInDays(Carbon::parse($data['start_date']));
        $data['price'] = Office::find($data['office_id'])->price_per_day * $count_days;
        return $data;
    }
}
