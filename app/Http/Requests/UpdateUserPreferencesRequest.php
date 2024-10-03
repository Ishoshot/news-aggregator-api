<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateUserPreferencesRequest extends FormRequest
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
            'article_sources' => ['bail', 'array', 'required'],
            'article_sources.*' => ['bail', 'string', 'exists:article_sources,id'],

            'article_categories' => ['bail', 'array', 'required'],
            'article_categories.*' => ['bail', 'string', 'exists:article_categories,id'],

            'article_authors' => ['bail', 'array', 'required'],
            'article_authors.*' => ['bail', 'string', 'exists:article_authors,id'],
        ];
    }
}
