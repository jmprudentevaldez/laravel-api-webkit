<?php

namespace App\Http\Requests\Address;

use Illuminate\Foundation\Http\FormRequest;

class ProvinceRequest extends FormRequest
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
            'provinces.index' => $this->getFetchProvincesRules(),
            default => [],
        };
    }

    /**
     * Fetch rules
     */
    private function getFetchProvincesRules(): array
    {
        return [
            'region' => ['string'],
            'code' => ['string'],
        ];
    }

    /**
     * Custom message for validation
     */
    public function messages(): array
    {
        return [
            'exists.region' => 'The :attribute ID does not exists',
        ];
    }
}
