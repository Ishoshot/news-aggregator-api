<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UserArticleSearchRequest extends FormRequest
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
            'keyword' => ['bail', 'nullable', 'string'],
            'date' => ['bail', 'nullable', 'date'],
            'start_date' => ['bail', 'nullable', 'date'],
            'end_date' => ['bail', 'nullable', 'date', 'after_or_equal:start_date'],
            'order' => ['bail', 'nullable', 'string', 'in:asc,desc'],
            'per_page' => ['bail', 'nullable', 'integer', 'min:1', 'max:100'],
            'filter_logic' => ['bail', 'nullable', 'string', 'in:and,or'],
        ];
    }
}
