<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Article;
use App\Models\ArticleAuthor;
use App\Models\ArticleCategory;
use App\Models\ArticleSource;
use Database\Factories\Concerns\RefreshOnCreate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 */
final class ArticleFactory extends Factory
{
    /**
     * @use RefreshOnCreate<Article>
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
            'article_source_id' => ArticleSource::factory(),
            'article_author_id' => ArticleAuthor::factory(),
            'article_category_id' => ArticleCategory::factory(),
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'content' => $this->faker->paragraphs(3, true),
            'article_url' => $this->faker->url,
            'cover_image_url' => $this->faker->imageUrl,
            'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
