<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EmailOrMobile implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Check if the value is a valid email
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        // Check if the value is a valid mobile number (assuming 10 digits)
        if (preg_match('/^\d{10}$/', $value)) {
            return;
        }

        $fail('The :attribute must be a valid email address or a 10-digit mobile number.');
    }
}
