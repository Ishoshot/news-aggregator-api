<?php

declare(strict_types=1);

namespace Tests\Feature\Preference;

use App\Models\ArticleAuthor;
use App\Models\ArticleCategory;
use App\Models\ArticleSource;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('fails validation when data is invalid', function (): void {

    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $data = [
        'article_sources' => [],
        'article_categories' => [],
        'article_authors' => [],
    ];

    $response = $this->postJson(route('user.preference.store'), $data);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['article_sources', 'article_categories', 'article_authors']);
});

it('updates user preferences successfully', function (): void {

    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $articleSource = ArticleSource::factory()->create();

    $articleCategory = ArticleCategory::factory()->create();

    $articleAuthor = ArticleAuthor::factory()->create();

    $data = [
        'article_sources' => [$articleSource->id],
        'article_categories' => [$articleCategory->id],
        'article_authors' => [$articleAuthor->id],
    ];

    $response = $this->postJson(route('user.preference.store'), $data);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Article preferences updated successfully.']);

});

it('updates user preferences with multiple values successfully', function (): void {

    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $articleSources = ArticleSource::factory(3)->create();

    $articleCategories = ArticleCategory::factory(3)->create();

    $articleAuthors = ArticleAuthor::factory(3)->create();

    $data = [
        'article_sources' => $articleSources->pluck('id')->toArray(),
        'article_categories' => $articleCategories->pluck('id')->toArray(),
        'article_authors' => $articleAuthors->pluck('id')->toArray(),
    ];

    $response = $this->postJson(route('user.preference.store'), $data);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Article preferences updated successfully.']);

    expect($user->articleSources)->toHaveCount(3);
    expect($user->articleCategories)->toHaveCount(3);
    expect($user->articleAuthors)->toHaveCount(3);
});
