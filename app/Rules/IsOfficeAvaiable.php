<?php

namespace App\Rules;

use App\Models\Office;
use App\Models\Reservation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IsOfficeAvaiable implements ValidationRule
{
    public function __construct(public $data)
    {
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $data = $this->data;
        $is_office_available = Reservation::filterdByTime($data['start_date'], $data['end_date'])
            ->filterdByOfficeId($value)->filterdByStatus(Reservation::STATUS_ACTIVE)->count();
        if ($is_office_available > 0) {
            $fail(trans('validation.office_not_available'));
        }
        if (Office::find($value)->user_id == auth()->id()) {
            $fail(trans('validation.cant make reservation to your office'));
        }
    }
}
