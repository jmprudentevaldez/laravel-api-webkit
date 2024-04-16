<?php

namespace App\Http\Requests\Address;

use App\Enums\BarangayClassification;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class BarangayRequest extends FormRequest
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
            'barangays.index' => $this->getFetchBarangayRules(),
            default => [],
        };
    }

    /**
     * Fetch rules
     */
    private function getFetchBarangayRules(): array
    {
        return [
            'city' => ['string'],
            'code' => ['string'],
            'classification' => [new Enum(BarangayClassification::class)],
        ];
    }

    /**
     * Custom message for validation
     */
    public function messages(): array
    {
        return [
            // As of writing, we need to add the namespace for the enum rule
            'classification.Illuminate\Validation\Rules\Enum' => 'Valid values for the :attribute field are `rural` and `urban`.',
        ];
    }
}
