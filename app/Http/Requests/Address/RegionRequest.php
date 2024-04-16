<?php

namespace App\Http\Requests\Address;

use Illuminate\Foundation\Http\FormRequest;

class RegionRequest extends FormRequest
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
            'regions.index' => $this->getFetchRegionsRules(),
            default => [],
        };
    }

    /**
     * Fetch rules
     */
    private function getFetchRegionsRules(): array
    {
        return [
            'code' => ['string'],
        ];
    }
}
