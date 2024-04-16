<?php

namespace App\Http\Requests;

use App\Enums\SexualCategory;
use App\Rules\DbVarcharMaxLength;
use App\Rules\InternationalPhoneNumberFormat;
use App\Rules\PhoneCountryFormat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    private string $dateToday;

    public function __construct(
        array $query = [],
        array $request = [],
        array $attributes = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        $content = null
    ) {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
        $this->dateToday = date('Y-m-d');
    }

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
            'users.store' => $this->getStoreUserRules(),
            'users.update' => $this->getUpdateUserRules(),
            'users.index' => $this->getFetchUsersRules(),
            'users.upload.profile-picture' => $this->getUploadProfilePictureRules(),
            'users.search' => $this->getSearchUsersRules(),
            default => [],
        };
    }

    /**
     * User store rules
     */
    public function getStoreUserRules(): array
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
            'telephone_number' => [
                'nullable',
                new InternationalPhoneNumberFormat(),
                new PhoneCountryFormat('PH'),
                'phone:fixed_line',
            ],
            'sex' => ['nullable', new Enum(SexualCategory::class)],
            'birthday' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:'.$this->dateToday],
            'home_address' => ['string', 'nullable'],
            'barangay_id' => ['nullable', 'exists:barangays,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'province_id' => ['nullable', 'exists:provinces,id'],
            'region_id' => ['nullable', 'exists:regions,id'],
            'postal_code' => ['nullable', new DbVarcharMaxLength()],
            'profile_picture_path' => ['string', 'nullable', new DbVarcharMaxLength()],
            'active' => ['nullable', 'boolean'],
            'email_verified' => ['nullable', 'boolean'],
            'roles' => ['nullable', 'array', 'max:25'],
            'roles.*' => ['required', 'exists:roles,id', 'distinct'],
        ];
    }

    /**
     * User update rules
     */
    private function getUpdateUserRules(): array
    {
        return [
            'email' => ['email', 'unique:users,email,'.request('id')],
            'password' => ['string', 'confirmed', 'max:100', Password::min(8)->mixedCase()->numbers()],
            'first_name' => ['string', new DbVarcharMaxLength()],
            'last_name' => ['string', new DbVarcharMaxLength()],
            'middle_name' => ['string', 'nullable', new DbVarcharMaxLength()],
            'ext_name' => ['string', 'nullable', new DbVarcharMaxLength()],
            'mobile_number' => [
                'nullable',
                'unique:user_profiles,mobile_number,'.request('id'),
                new InternationalPhoneNumberFormat(),
                new PhoneCountryFormat('PH'),
                'phone:mobile',
            ],
            'telephone_number' => [
                'nullable',
                new InternationalPhoneNumberFormat(),
                new PhoneCountryFormat('PH'),
                'phone:fixed_line',
            ],
            'sex' => ['nullable', new Enum(SexualCategory::class)],
            'birthday' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:'.$this->dateToday],
            'home_address' => ['string', 'nullable'],
            'barangay_id' => ['nullable', 'exists:barangays,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'province_id' => ['nullable', 'exists:provinces,id'],
            'region_id' => ['nullable', 'exists:regions,id'],
            'postal_code' => ['nullable', new DbVarcharMaxLength()],
            'profile_picture_path' => ['string', 'nullable', new DbVarcharMaxLength()],
            'active' => ['nullable', 'boolean'],
            'email_verified' => ['nullable', 'boolean'],
            'roles' => ['nullable', 'array', 'max:25'],
            'roles.*' => ['required', 'exists:roles,id', 'distinct'],
        ];
    }

    /**
     * User update rules
     */
    private function getFetchUsersRules(): array
    {
        return [
            'active' => ['boolean'],
            'verified' => ['boolean'],
            'role' => ['integer', 'min:1'],
            'sort' => ['in:asc,desc'],
            'sort_by' => ['string'],
            'limit' => ['int'],
            'page' => ['int'],
            'email' => ['email'],
        ];
    }

    /**
     * User search rules
     */
    private function getSearchUsersRules(): array
    {
        return [
            'query' => ['required', 'string'],
        ];
    }

    /**
     * Profile photo upload rules
     */
    private function getUploadProfilePictureRules(): array
    {
        return [
            'photo' => ['max:5120', 'required', 'image'], // 5Mb max
        ];
    }

    /**
     * Set the email to lowercase
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge(['email' => strtolower($this->get('email'))]);
        }
    }

    /**
     * Custom message for validation
     */
    public function messages(): array
    {
        return [
            'sort.in' => 'The :attribute parameter must be either `asc` or `desc`',
            'active.boolean' => 'The :attribute parameter must be either `1` (for true) or `0` (for false)',
            'verified.boolean' => 'The :attribute parameter must be either `1` (for true) or `0` (for false)',
            'country_id.exists' => 'The :attribute does not exists',
            'photo.max' => 'The :attribute must not exceed 2MB',
            'roles.array' => 'The :attribute field must be an array of role names',
            'roles.*.exists' => 'The role ID does not exists',
            'birthday.before_or_equal' => 'The :attribute field must not be greater than today',

            /** @see https://github.com/Propaganistas/Laravel-Phone#validation */
            'mobile_number.phone' => 'The :attribute field format must be a valid mobile number',
            'telephone_number.phone' => 'The :attribute field format must be a valid line number',

            // As of writing, we need to add the namespace for the enum rule
            'sex.Illuminate\Validation\Rules\Enum' => 'Valid values for the :attribute field are `male` and `female`.',
        ];
    }
}
