<?php

declare(strict_types=1);

namespace Tests\Feature\Article;

use App\Models\Article;
use App\Models\ArticleAuthor;
use App\Models\ArticleCategory;
use App\Models\ArticleSource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;

it('returns error for unauthenticated users', function (): void {

    Article::factory()->count(5)->create();

    $response = $this->getJson(route('user.personalized.article.list'));

    $response->assertStatus(401);
});

it('fetches personalized articles based on user preferences', function (): void {

    $user = User::factory()->create();

    $articleSources = ArticleSource::factory(2)->create();

    $articleCategories = ArticleCategory::factory(2)->create();

    $articleAuthors = ArticleAuthor::factory(2)->create();

    // Attach preferences to the user
    $user->articleSources()->attach($articleSources);
    $user->articleCategories()->attach($articleCategories);
    $user->articleAuthors()->attach($articleAuthors);

    Sanctum::actingAs($user);

    Article::factory()->create([
        'article_source_id' => $articleSources[0]->id,
        'article_category_id' => $articleCategories[0]->id,
        'article_author_id' => $articleAuthors[0]->id,
        'published_at' => Carbon::now(),
    ]);

    Article::factory(2)->create();

    $response = $this->getJson(route('user.personalized.article.list'));

    $response->assertStatus(200)
        ->assertJson(fn (AssertableJson $json) => $json->where('message', 'Articles retrieved successfully.')
            ->has('data.data', 1) // Only one article matches the preferences
            ->etc()
        );
});

it('fetches personalized articles with no preferences set', function (): void {

    $user = User::factory()->create();

    Sanctum::actingAs($user);

    Article::factory(5)->create();

    $response = $this->getJson(route('user.personalized.article.list'));

    $response->assertStatus(200)
        ->assertJson(fn (AssertableJson $json) => $json->where('message', 'Articles retrieved successfully.')
            ->has('data.data', 5) // All 5 articles should be returned
            ->etc()
        );
});

it('fetches personalized articles based on multiple sources and categories', function (): void {

    $user = User::factory()->create();

    $articleSources = ArticleSource::factory(3)->create();

    $articleCategories = ArticleCategory::factory(3)->create();

    // Attach multiple sources and categories
    $user->articleSources()->attach($articleSources);
    $user->articleCategories()->attach($articleCategories);

    Sanctum::actingAs($user);

    Article::factory()->create([
        'article_source_id' => $articleSources[0]->id,
        'article_category_id' => $articleCategories[0]->id,
        'published_at' => Carbon::now(),
    ]);

    Article::factory()->create([
        'article_source_id' => $articleSources[1]->id,
        'article_category_id' => $articleCategories[1]->id,
        'published_at' => Carbon::now(),
    ]);

    Article::factory(3)->create();

    $response = $this->getJson(route('user.personalized.article.list'));

    $response->assertStatus(200)
        ->assertJson(fn (AssertableJson $json) => $json->where('message', 'Articles retrieved successfully.')
            ->has('data.data', 2) // Two articles match the preferences
            ->etc()
        );
});

it('filters personalized articles by keyword', function (): void {

    $user = User::factory()->create();

    Sanctum::actingAs($user);

    // Create articles, one with a specific keyword
    Article::factory()->create(['title' => 'Laravel Best Practices']);
    Article::factory()->create(['title' => 'PHP Tips and Tricks']);

    $response = $this->getJson(route('user.personalized.article.list', ['keyword' => 'Laravel']));

    $response->assertStatus(200)
        ->assertJson(fn (AssertableJson $json) => $json->where('message', 'Articles retrieved successfully.')
            ->has('data.data', 1) // Only one article should match the keyword
            ->etc()
        );
});

it('sorts personalized articles by published date', function (): void {

    $user = User::factory()->create();

    Sanctum::actingAs($user);

    // Create articles with different published dates
    $article1 = Article::factory()->create(['published_at' => Carbon::now()->subDays(10)]);
    $article2 = Article::factory()->create(['published_at' => Carbon::now()]);

    $response = $this->getJson(route('user.personalized.article.list', ['order' => 'asc']));

    $response->assertStatus(200)
        ->assertJson(fn (AssertableJson $json) => $json->where('message', 'Articles retrieved successfully.')
            ->has('data.data', 2)
            ->where('data.data.0.id', $article1->id) // Oldest article first
            ->where('data.data.1.id', $article2->id) // Newest article second
            ->etc()
        );
});

it('fetches personalized articles with pagination', function (): void {

    $user = User::factory()->create();

    Sanctum::actingAs($user);

    Article::factory(15)->create();

    $response = $this->getJson(route('user.personalized.article.list', ['per_page' => 5]));

    // Assert pagination
    $response->assertStatus(200)
        ->assertJson(fn (AssertableJson $json) => $json->where('message', 'Articles retrieved successfully.')
            ->has('data.data', 5) // 5 articles per page
            ->has('data.links')
            ->etc()
        );
});

it('sets user preferences and fetches personalized articles', function (): void {

    $user = User::factory()->create();

    Sanctum::actingAs($user);

    // Create sources, categories, and authors for articles
    $articleSources = ArticleSource::factory(3)->create();
    $articleCategories = ArticleCategory::factory(3)->create();
    $articleAuthors = ArticleAuthor::factory(3)->create();

    // Prepare data to set preferences
    $preferenceData = [
        'article_sources' => $articleSources->pluck('id')->toArray(),
        'article_categories' => $articleCategories->pluck('id')->toArray(),
        'article_authors' => $articleAuthors->pluck('id')->toArray(),
    ];

    $response = $this->postJson(route('user.preference.store'), $preferenceData);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Article preferences updated successfully.']);

    // Verify that the user's preferences have been saved
    expect($user->articleSources()->count())->toBe(3);
    expect($user->articleCategories()->count())->toBe(3);
    expect($user->articleAuthors()->count())->toBe(3);

    // Create articles matching the user's preferences
    $matchingArticle1 = Article::factory()->create([
        'article_source_id' => $articleSources[0]->id,
        'article_category_id' => $articleCategories[0]->id,
        'article_author_id' => $articleAuthors[0]->id,
        'published_at' => Carbon::now(),
    ]);

    $matchingArticle2 = Article::factory()->create([
        'article_source_id' => $articleSources[1]->id,
        'article_category_id' => $articleCategories[1]->id,
        'article_author_id' => $articleAuthors[1]->id,
        'published_at' => Carbon::now()->subDay(),
    ]);

    Article::factory(7)->create(); // No matching preferences

    // Call the endpoint to list personalized articles
    $listResponse = $this->getJson(route('user.personalized.article.list'));

    $listResponse->assertStatus(200)
        ->assertJson(fn (AssertableJson $json) => $json->where('message', 'Articles retrieved successfully.')
            ->has('data.data', 2) // Two articles match the preferences
            ->where('data.data.0.id', $matchingArticle1->id)
            ->where('data.data.1.id', $matchingArticle2->id)
            ->etc()
        );
});
