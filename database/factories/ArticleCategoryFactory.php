<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ArticleCategory;
use Database\Factories\Concerns\RefreshOnCreate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArticleCategory>
 */
final class ArticleCategoryFactory extends Factory
{
    /**
     * @use RefreshOnCreate<ArticleCategory>
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
            'name' => $this->faker->company,
            'slug' => $this->faker->slug,
        ];
    }
}
