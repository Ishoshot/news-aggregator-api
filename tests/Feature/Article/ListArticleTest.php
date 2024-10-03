<?php

declare(strict_types=1);

namespace Tests\Feature\Article;

use App\Models\Article;
use App\Models\ArticleAuthor;
use App\Models\ArticleCategory;
use App\Models\ArticleSource;
use App\Models\User;
use App\Services\Internal\ArticleService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;

it('returns error for unauthenticated users', function (): void {

    Article::factory()->count(5)->create();

    $response = $this->getJson(route('user.article.list'));

    $response->assertStatus(401);
});

it('retrieves all articles', function (): void {

    $user = User::factory()->create();

    Sanctum::actingAs($user);

    Article::factory()->count(5)->create();

    $response = $this->getJson(route('user.article.list'));

    $response->assertStatus(200)
        ->assertJson(fn (AssertableJson $json) => $json->where('message', 'Articles retrieved successfully.')
            ->has('data.data', 5)
            ->etc()
        );
});

it('filters articles by keyword', function (): void {

    $user = User::factory()->create();

    Sanctum::actingAs($user);

    Article::factory()->create(['title' => 'PHP Testing Best Practices']);

    Article::factory()->create(['title' => 'JavaScript for Beginners']);

    $response = $this->getJson(route('user.article.list', ['keyword' => 'PHP']));

    $response->assertStatus(200)
        ->assertJson(fn (AssertableJson $json) => $json->where('message', 'Articles retrieved successfully.')
            ->has('data.data', 1)
            ->where('data.data.0.title', 'PHP Testing Best Practices')
        );
});

it('filters articles by category', function (): void {

    $user = User::factory()->create();

    Sanctum::actingAs($user);

    Article::factory()->create(['article_category_id' => $categoryId = ArticleCategory::factory()->create()->id]);

    Article::factory()->create(['article_category_id' => $anotherCategoryId = ArticleCategory::factory()->create()->id]);

    $response = $this->getJson(route('user.article.list', ['category' => $categoryId]));

    $response->assertStatus(200)
        ->assertJson(fn (AssertableJson $json) => $json->where('message', 'Articles retrieved successfully.')
            ->has('data.data', 1)
            ->where('data.data.0.article_category_id', $categoryId)
        );
});

it('filters articles by source', function (): void {

    $user = User::factory()->create();

    Sanctum::actingAs($user);

    Article::factory()->create(['article_source_id' => $sourceId = ArticleSource::factory()->create()->id]);

    Article::factory()->create(['article_source_id' => $anotherSourceId = ArticleSource::factory()->create()->id]);

    $response = $this->getJson(route('user.article.list', ['source' => $sourceId]));

    $response->assertStatus(200)
        ->assertJson(fn (AssertableJson $json) => $json->where('message', 'Articles retrieved successfully.')
            ->has('data.data', 1)
            ->where('data.data.0.article_source_id', $sourceId)
        );
});

it('filters articles by author', function (): void {

    $user = User::factory()->create();

    Sanctum::actingAs($user);

    Article::factory()->create(['article_author_id' => $authorId = ArticleAuthor::factory()->create()->id]);

    Article::factory()->create(['article_author_id' => $anotherAuthorId = ArticleAuthor::factory()->create()->id]);

    $response = $this->getJson(route('user.article.list', ['author' => $anotherAuthorId]));

    $response->assertStatus(200)
        ->assertJson(fn (AssertableJson $json) => $json->where('message', 'Articles retrieved successfully.')
            ->has('data.data', 1)
            ->where('data.data.0.article_author_id', $anotherAuthorId)
        );
});

it('filters articles by exact date', function (): void {

    $user = User::factory()->create();

    Sanctum::actingAs($user);

    Article::factory()->create(['published_at' => Carbon::now()]);

    Article::factory()->create(['published_at' => Carbon::now()->subDays(5)]);

    $response = $this->getJson(route('user.article.list', ['date' => Carbon::now()->toDateString()]));

    $response->assertStatus(200)
        ->assertJson(fn (AssertableJson $json) => $json->where('message', 'Articles retrieved successfully.')
            ->has('data.data', 1)
        );
});

it('filters articles by start date', function (): void {

    $user = User::factory()->create();

    Sanctum::actingAs($user);

    Article::factory()->create(['published_at' => Carbon::now()]);

    Article::factory()->create(['published_at' => Carbon::now()->subDays(10)]);

    $response = $this->getJson(route('user.article.list', ['start_date' => Carbon::now()->subDays(5)->toDateString()]));

    $response->assertStatus(200)
        ->assertJson(fn (AssertableJson $json) => $json->where('message', 'Articles retrieved successfully.')
            ->has('data.data', 1)
        );
});

it('filters articles by end date', function (): void {

    $user = User::factory()->create();

    Sanctum::actingAs($user);

    Article::factory()->create(['published_at' => Carbon::now()]);

    Article::factory()->create(['published_at' => Carbon::now()->subDays(10)]);

    $response = $this->getJson(route('user.article.list', ['end_date' => Carbon::now()->subDays(5)->toDateString()]));

    $response->assertStatus(200)
        ->assertJson(fn (AssertableJson $json) => $json->where('message', 'Articles retrieved successfully.')
            ->has('data.data', 1)
        );
});

it('sorts articles by published date in ascending order', function (): void {

    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $article1 = Article::factory()->create(['published_at' => Carbon::now()]);

    $article2 = Article::factory()->create(['published_at' => Carbon::now()->subDays(10)]);

    $response = $this->getJson(route('user.article.list', ['order' => 'asc']));

    $response->assertStatus(200)
        ->assertJson(fn (AssertableJson $json) => $json->where('message', 'Articles retrieved successfully.')
            ->has('data.data', 2)
        );

    // Assert that the articles are sorted in ascending order
    $articles = $response->json('data.data');
    $this->assertEquals($article2->id, $articles[0]['id']);  // Oldest article first
    $this->assertEquals($article1->id, $articles[1]['id']);  // Newest article second
});

it('sorts articles by published date in descending order', function (): void {

    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $article1 = Article::factory()->create(['published_at' => Carbon::now()]);

    $article2 = Article::factory()->create(['published_at' => Carbon::now()->subDays(10)]);

    $response = $this->getJson(route('user.article.list', ['order' => 'desc']));

    $response->assertStatus(200)
        ->assertJson(fn (AssertableJson $json) => $json->where('message', 'Articles retrieved successfully.')
            ->has('data.data', 2)
        );

    // Assert that the articles are sorted in descending order
    $articles = $response->json('data.data');
    $this->assertEquals($article1->id, $articles[0]['id']);  // Newest article first
    $this->assertEquals($article2->id, $articles[1]['id']);  // Oldest article second
});

it('returns empty list if no articles match the criteria', function (): void {

    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->getJson(route('user.article.list', ['keyword' => 'world']));

    $response->assertStatus(200)
        ->assertJson(fn (AssertableJson $json) => $json->where('message', 'Articles retrieved successfully.')
            ->has('data.data', 0)
        );
});

it('handles exceptions when retrieving articles', function () {

    $this->instance(
        ArticleService::class,
        mock(new ArticleService(), function ($mock): void {
            $mock->shouldReceive('filter')
                ->andThrow(new Exception('Something went wrong'));
        })
    );

    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->getJson(route('user.article.list'));

    $response->assertStatus(500);

    Log::shouldReceive('error')->with('Error occurred while retrieving articles: Some error');
});

it('filters articles by multiple sources and categories', function (): void {

    $user = User::factory()->create();

    Sanctum::actingAs($user);

    Article::factory(10)->create();

    $sourceIds = ArticleSource::take(4)->pluck('id')->toArray();

    $categoryIds = ArticleCategory::take(3)->pluck('id')->toArray();

    // Pass sources and categories as a comma-separated string
    $response = $this->getJson(route('user.article.list', [
        'source' => implode(',', $sourceIds),
        'category' => implode(',', $categoryIds),
    ]));

    $response->assertStatus(200)
        ->assertJson(fn (AssertableJson $json) => $json->where('message', 'Articles retrieved successfully.')
            ->has('data.data', 3)// This will be 3 because the result will always contain the intersection between the filters
        );

    // Assert that the response contains articles with expected source and category IDs
    foreach ($response->json('data.data') as $article) {
        $this->assertContains($article['article_source_id'], $sourceIds);
        $this->assertContains($article['article_category_id'], $categoryIds);
    }
});
