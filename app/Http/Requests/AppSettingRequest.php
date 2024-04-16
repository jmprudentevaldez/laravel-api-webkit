<?php

namespace App\Http\Requests;

use App\Enums\AppTheme;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class AppSettingRequest extends FormRequest
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
            'app-settings.store' => $this->getStoreSettingsRules(),
            default => [],
        };
    }

    /**
     * Get email availability rules
     */
    private function getStoreSettingsRules(): array
    {
        return [
            'theme' => ['required', new Enum(AppTheme::class)],
        ];
    }
}
