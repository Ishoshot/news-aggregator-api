<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ArticleAuthor;
use Database\Factories\Concerns\RefreshOnCreate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArticleAuthor>
 */
final class ArticleAuthorFactory extends Factory
{
    /**
     * @use RefreshOnCreate<ArticleAuthor>
     */
    use RefreshOnCreate;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'slug' => $this->faker->slug,
        ];
    }
}
