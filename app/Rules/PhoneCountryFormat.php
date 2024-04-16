<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use Propaganistas\LaravelPhone\PhoneNumber;

class PhoneCountryFormat implements ValidationRule
{
    private string $countryCode;

    public function __construct(string $countryCode)
    {
        $this->countryCode = $countryCode;
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $phoneNumber = new PhoneNumber($value);

        if (! $phoneNumber->isOfCountry($this->countryCode)) {
            $countryCodeUpper = strtoupper($this->countryCode);
            $fail("The :attribute field format must be a valid $countryCodeUpper mobile number");
        }
    }
}
