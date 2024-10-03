<?php

declare(strict_types=1);

namespace Tests\Feature\Preference;

use App\Models\ArticleAuthor;
use App\Models\ArticleCategory;
use App\Models\ArticleSource;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

it('retrieves article preferences for authenticated users', function () {

    $user = User::factory()->create();

    $articleSources = ArticleSource::factory()->count(2)->create();

    $articleCategories = ArticleCategory::factory()->count(2)->create();

    $articleAuthors = ArticleAuthor::factory()->count(2)->create();

    $user->articleSources()->attach($articleSources->pluck('id')->toArray());
    $user->articleCategories()->attach($articleCategories->pluck('id')->toArray());
    $user->articleAuthors()->attach($articleAuthors->pluck('id')->toArray());

    Sanctum::actingAs($user);

    $response = $this->getJson(route('user.preference.list'));

    $response->assertStatus(200);

    $response->assertJsonStructure([
        'message',
        'data' => [
            'article_sources' => [
                '*' => ['id', 'name', 'created_at', 'updated_at'],
            ],
            'article_categories' => [
                '*' => ['id', 'name', 'created_at', 'updated_at'],
            ],
            'article_authors' => [
                '*' => ['id', 'name', 'created_at', 'updated_at'],
            ],
        ],
    ]);

    $response->assertJsonCount(2, 'data.article_sources');

    $response->assertJsonCount(2, 'data.article_categories');

    $response->assertJsonCount(2, 'data.article_authors');
});

it('retrieves the correct article preference values for authenticated users', function () {

    $user = User::factory()->create();

    $articleSource1 = ArticleSource::factory()->create(['name' => 'TechCrunch', 'slug' => Str::slug('TechCrunch')]);

    $articleSource2 = ArticleSource::factory()->create(['name' => 'BBC News', 'slug' => Str::slug('BBC News')]);

    $articleCategory1 = ArticleCategory::factory()->create(['name' => 'Technology', 'slug' => Str::slug('Technology')]);

    $articleCategory2 = ArticleCategory::factory()->create(['name' => 'Politics', 'slug' => Str::slug('Politics')]);

    $articleAuthor1 = ArticleAuthor::factory()->create(['name' => 'John Doe', 'slug' => Str::slug('John Doe')]);

    $articleAuthor2 = ArticleAuthor::factory()->create(['name' => 'Jane Smith', 'slug' => Str::slug('Jane Smith')]);

    $user->articleSources()->attach([$articleSource1->id, $articleSource2->id]);

    $user->articleCategories()->attach([$articleCategory1->id, $articleCategory2->id]);

    $user->articleAuthors()->attach([$articleAuthor1->id, $articleAuthor2->id]);

    Sanctum::actingAs($user);

    $response = $this->getJson(route('user.preference.list'));

    $response->assertStatus(200);

    $response->assertJsonFragment([
        'data' => [
            'article_sources' => [
                [
                    'id' => $articleSource1->id,
                    'name' => 'TechCrunch',
                    'slug' => $articleSource1->slug,
                    'created_at' => $articleSource1->created_at->toISOString(),
                    'updated_at' => $articleSource1->updated_at->toISOString(),
                ],
                [
                    'id' => $articleSource2->id,
                    'name' => 'BBC News',
                    'slug' => $articleSource2->slug,
                    'created_at' => $articleSource2->created_at->toISOString(),
                    'updated_at' => $articleSource2->updated_at->toISOString(),
                ],
            ],
            'article_categories' => [
                [
                    'id' => $articleCategory1->id,
                    'name' => 'Technology',
                    'slug' => $articleCategory1->slug,
                    'created_at' => $articleCategory1->created_at->toISOString(),
                    'updated_at' => $articleCategory1->updated_at->toISOString(),
                ],
                [
                    'id' => $articleCategory2->id,
                    'name' => 'Politics',
                    'slug' => $articleCategory2->slug,
                    'created_at' => $articleCategory2->created_at->toISOString(),
                    'updated_at' => $articleCategory2->updated_at->toISOString(),
                ],
            ],
            'article_authors' => [
                [
                    'id' => $articleAuthor1->id,
                    'name' => 'John Doe',
                    'slug' => $articleAuthor1->slug,
                    'created_at' => $articleAuthor1->created_at->toISOString(),
                    'updated_at' => $articleAuthor1->updated_at->toISOString(),
                ],
                [
                    'id' => $articleAuthor2->id,
                    'name' => 'Jane Smith',
                    'slug' => $articleAuthor2->slug,
                    'created_at' => $articleAuthor2->created_at->toISOString(),
                    'updated_at' => $articleAuthor2->updated_at->toISOString(),
                ],
            ],
        ],
    ]);

});

it('sets and retrieves the correct article preference values for authenticated users', function () {

    $user = User::factory()->create();

    $articleSource1 = ArticleSource::factory()->create(['name' => 'TechCrunch', 'slug' => Str::slug('TechCrunch')]);

    $articleSource2 = ArticleSource::factory()->create(['name' => 'BBC News', 'slug' => Str::slug('BBC News')]);

    $articleCategory1 = ArticleCategory::factory()->create(['name' => 'Technology', 'slug' => Str::slug('Technology')]);

    $articleCategory2 = ArticleCategory::factory()->create(['name' => 'Politics', 'slug' => Str::slug('Politics')]);

    $articleAuthor1 = ArticleAuthor::factory()->create(['name' => 'John Doe', 'slug' => Str::slug('John Doe')]);

    $articleAuthor2 = ArticleAuthor::factory()->create(['name' => 'Jane Smith', 'slug' => Str::slug('Jane Smith')]);

    Sanctum::actingAs($user);

    $data = [
        'article_sources' => [$articleSource1->id, $articleSource2->id],
        'article_categories' => [$articleCategory1->id, $articleCategory2->id],
        'article_authors' => [$articleAuthor1->id, $articleAuthor2->id],
    ];

    $this->postJson(route('user.preference.store'), $data)->assertStatus(200);

    $response = $this->getJson(route('user.preference.list'));

    $response->assertStatus(200);

    $response->assertJsonFragment([
        'data' => [
            'article_sources' => [
                [
                    'id' => $articleSource1->id,
                    'name' => 'TechCrunch',
                    'slug' => $articleSource1->slug,
                    'created_at' => $articleSource1->created_at->toISOString(),
                    'updated_at' => $articleSource1->updated_at->toISOString(),
                ],
                [
                    'id' => $articleSource2->id,
                    'name' => 'BBC News',
                    'slug' => $articleSource2->slug,
                    'created_at' => $articleSource2->created_at->toISOString(),
                    'updated_at' => $articleSource2->updated_at->toISOString(),
                ],
            ],
            'article_categories' => [
                [
                    'id' => $articleCategory1->id,
                    'name' => 'Technology',
                    'slug' => $articleCategory1->slug,
                    'created_at' => $articleCategory1->created_at->toISOString(),
                    'updated_at' => $articleCategory1->updated_at->toISOString(),
                ],
                [
                    'id' => $articleCategory2->id,
                    'name' => 'Politics',
                    'slug' => $articleCategory2->slug,
                    'created_at' => $articleCategory2->created_at->toISOString(),
                    'updated_at' => $articleCategory2->updated_at->toISOString(),
                ],
            ],
            'article_authors' => [
                [
                    'id' => $articleAuthor1->id,
                    'name' => 'John Doe',
                    'slug' => $articleAuthor1->slug,
                    'created_at' => $articleAuthor1->created_at->toISOString(),
                    'updated_at' => $articleAuthor1->updated_at->toISOString(),
                ],
                [
                    'id' => $articleAuthor2->id,
                    'name' => 'Jane Smith',
                    'slug' => $articleAuthor2->slug,
                    'created_at' => $articleAuthor2->created_at->toISOString(),
                    'updated_at' => $articleAuthor2->updated_at->toISOString(),
                ],
            ],
        ],
    ]);

});
