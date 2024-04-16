<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AvailabilityRequest extends FormRequest
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
            'availability.email' => $this->getEmailAvailabilityRules(),
            'availability.mobile_number' => $this->getMobileNumberAvailabilityRules(),
            default => []
        };
    }

    /**
     * Get email availability rules
     */
    private function getEmailAvailabilityRules(): array
    {
        return [
            'value' => ['required', 'email'],
            'exclude_id' => ['nullable'],
        ];
    }

    /**
     * Get mobile availability rules
     *
     * @return array[]
     */
    private function getMobileNumberAvailabilityRules(): array
    {
        return [
            'value' => ['required'],
            'exclude_id' => ['nullable'],
        ];
    }
}
