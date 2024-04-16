<?php

namespace App\Http\Requests;

use App\Enums\SexualCategory;
use App\Rules\DbVarcharMaxLength;
use App\Rules\InternationalPhoneNumberFormat;
use App\Rules\PhoneCountryFormat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

class AuthRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $routeName = $this->route()->getName();

        return match ($routeName) {
            'auth.store' => $this->getLoginRules(),
            'auth.revoke' => $this->getRevokeAccessRules(),
            'auth.password.forgot' => $this->getForgotPasswordRules(),
            'auth.password.reset' => $this->getResetPasswordRules(),
            'auth.register' => $this->getRegisterRules(),
            default => []
        };
    }

    /**
     * Get the login rules
     */
    private function getLoginRules(): array
    {
        return [
            'email' => ['email'],
            'mobile_number' => ['required_without:email', new PhoneCountryFormat('PH')],
            'password' => ['required', 'string'],
            'client_name' => ['nullable', 'string', new DbVarcharMaxLength()],
            'with_user' => ['nullable', 'bool'], // Send the token back with user information
        ];
    }

    /**
     * Get revoke access rules
     */
    private function getRevokeAccessRules(): array
    {
        return [
            'token_ids' => ['required', 'array'],
            'token_ids.*' => ['required'],
        ];
    }

    /**
     * Get forgot password rules
     */
    private function getForgotPasswordRules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:users,email'],
        ];
    }

    /**
     * Get forgot password rules
     */
    private function getResetPasswordRules(): array
    {
        return [
            'token' => ['required'],
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['string', 'nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'client_name' => ['nullable', 'string', new DbVarcharMaxLength()],
        ];
    }

    /**
     * Get register user rules
     */
    private function getRegisterRules(): array
    {
        return [
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['string', 'required', 'confirmed', 'max:100', Password::min(8)->mixedCase()->numbers()],
            'first_name' => ['string', 'required', new DbVarcharMaxLength()],
            'last_name' => ['string', 'required', new DbVarcharMaxLength()],
            'middle_name' => ['string', 'nullable', new DbVarcharMaxLength()],
            'ext_name' => ['string', 'nullable', new DbVarcharMaxLength()],
            'mobile_number' => [
                'nullable',
                'unique:user_profiles,mobile_number',
                new InternationalPhoneNumberFormat(),
                new PhoneCountryFormat('PH'),
                'phone:mobile',
            ],
            'sex' => ['nullable', new Enum(SexualCategory::class)],
            'birthday' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:'.$this->dateToday],
            'home_address' => ['string', 'nullable'],
            'barangay_id' => ['nullable', 'exists:barangays,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'province_id' => ['nullable', 'exists:provinces,id'],
            'region_id' => ['nullable', 'exists:regions,id'],
            'postal_code' => ['nullable', new DbVarcharMaxLength()],
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            'email.exists' => 'The :attribute is not registered',
            'birthday.before_or_equal' => 'The :attribute field must not be greater than today',
            'mobile_number.phone' => 'The :attribute field format must be a valid mobile number',
        ];
    }
}
