<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ArticleSource;
use Database\Factories\Concerns\RefreshOnCreate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArticleSource>
 */
final class ArticleSourceFactory extends Factory
{
    /**
     * @use RefreshOnCreate<ArticleSource>
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
