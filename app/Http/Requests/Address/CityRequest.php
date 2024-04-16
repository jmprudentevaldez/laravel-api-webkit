<?php

namespace App\Http\Requests\Address;

use App\Enums\MunicipalClassification;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class CityRequest extends FormRequest
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
            'cities.index' => $this->getFetchCitiesRules(),
            default => [],
        };
    }

    /**
     * Fetch rules
     */
    private function getFetchCitiesRules(): array
    {
        return [
            'province' => ['string'],
            'code' => ['string'],
            'classification' => [new Enum(MunicipalClassification::class)],
        ];
    }

    /**
     * Custom message for validation
     */
    public function messages(): array
    {
        return [
            'exists.region' => 'The :attribute ID does not exists',

            // As of writing, we need to add the namespace for the enum rule
            'classification.Illuminate\Validation\Rules\Enum' => 'Valid values for the :attribute field are `city` and `municipality`.',
        ];
    }
}
