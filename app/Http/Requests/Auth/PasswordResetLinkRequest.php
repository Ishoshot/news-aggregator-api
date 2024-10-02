<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Rules\UnauthorizedEmailProviders;
use Illuminate\Foundation\Http\FormRequest;

final class PasswordResetLinkRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['bail', 'required', 'string', 'lowercase', 'email', 'max:255', new UnauthorizedEmailProviders()],
        ];
    }
}
