<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Internal;

use App\Models\Article;
use App\Models\ArticleAuthor;
use App\Models\ArticleCategory;
use App\Models\ArticleSource;
use App\Services\Internal\ArticleService;
use Illuminate\Http\Request;

beforeEach(function (): void {
    $this->service = new ArticleService();
});

test('it can create a new article', function (): void {

    $data = [
        'title' => $title = fake()->sentence,
        'description' => fake()->paragraph,
        'content' => fake()->paragraphs(3, true),
        'article_url' => fake()->url,
        'cover_image_url' => fake()->imageUrl,
        'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
        'article_source_id' => ArticleSource::factory()->create()->id,
        'article_author_id' => ArticleAuthor::factory()->create()->id,
        'article_category_id' => ArticleCategory::factory()->create()->id,
    ];

    $article = $this->service->create($data);

    expect($article)->toBeInstanceOf(Article::class);

    expect($article->title)->toBe($title);
});

test('it can filter articles by keyword', function (): void {

    Article::factory()->create(['title' => 'Article One', 'description' => 'Description One']);

    Article::factory()->create(['title' => 'Article Two', 'description' => 'Description Two']);

    $request = new Request(['keyword' => 'One']);

    $query = Article::query();

    $this->service->filter($query, $request);

    $articles = $query->get();

    expect($articles)->toHaveCount(1);

    expect($articles->first()->title)->toBe('Article One');
});

test('it can filter articles by category', function (): void {

    Article::factory()->create(['title' => 'Article One', 'article_category_id' => $categoryId = ArticleCategory::factory()->create()->id]);

    Article::factory()->create(['title' => 'Article Two', 'article_category_id' => ArticleCategory::factory()->create()->id]);

    $request = new Request(['category' => $categoryId]);

    $query = Article::query();

    $this->service->filter($query, $request);

    $articles = $query->get();

    expect($articles)->toHaveCount(1);

    expect($articles->first()->title)->toBe('Article One');
});

test('it can sort articles by published date', function (): void {

    Article::factory()->create(['title' => 'Article A', 'published_at' => now()->subDays(1)]);

    Article::factory()->create(['title' => 'Article B', 'published_at' => now()]);

    $request = new Request(['order' => 'asc']);

    $query = Article::query();

    $this->service->sort($query, $request);

    $articles = $query->get();

    expect($articles->first()->title)->toBe('Article A');
});

test('it can filter articles by published date', function (): void {

    Article::factory()->create(['title' => 'Article A', 'published_at' => now()->subDays(2)]);

    Article::factory()->create(['title' => 'Article B', 'published_at' => now()]);

    $request = new Request(['date' => now()->subDays(2)->toDateString()]);

    $query = Article::query();

    $this->service->filter($query, $request);

    $articles = $query->get();

    expect($articles)->toHaveCount(1);

    expect($articles->first()->title)->toBe('Article A');
});

test('it can handle multiple filters', function (): void {

    Article::factory(5)->create();

    Article::factory()->create([
        'title' => 'Article 1',
        'article_category_id' => $categoryId = ArticleCategory::factory()->create()->id,
        'article_source_id' => $sourceId = ArticleSource::factory()->create()->id,
        'article_author_id' => $authorId = ArticleAuthor::factory()->create()->id,
    ]);

    $request = new Request([
        'category' => $categoryId,
        'source' => $sourceId,
        'author' => $authorId,
    ]);

    $query = Article::query();

    $this->service->filter($query, $request);

    $articles = $query->get();

    expect($articles)->toHaveCount(1);

    expect($articles->first()->title)->toBe('Article 1');
});
