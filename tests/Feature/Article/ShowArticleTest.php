<?php

declare(strict_types=1);

namespace Tests\Feature\Article;

use App\Models\Article;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('fails to retrieve article details for unauthenticated users', function () {

    $article = Article::factory()->create();

    $response = $this->getJson(route('user.article.show', $article->id));

    $response->assertStatus(401);
});

it('retrieve article details for authenticated users', function () {

    $user = User::factory()->create();

    $article = Article::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->getJson(route('user.article.show', $article->id));

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Article details retrieved successfully.',
            'data' => [
                'id' => $article->id,
                'article_source_id' => $article->article_source_id,
                'article_category_id' => $article->article_category_id,
                'article_author_id' => $article->article_author_id,
                'title' => $article->title,
                'description' => $article->description,
                'content' => $article->content,
                'article_url' => $article->article_url,
                'cover_image_url' => $article->cover_image_url,
                'published_at' => $article->published_at->toISOString(),
                'source' => [
                    'id' => $article->source->id,
                    'name' => $article->source->name,
                    'slug' => $article->source->slug,
                ],
                'author' => [
                    'id' => $article->author->id,
                    'name' => $article->author->name,
                    'slug' => $article->author->slug,
                ],
                'category' => [
                    'id' => $article->category->id,
                    'name' => $article->category->name,
                    'slug' => $article->category->slug,
                ],
            ],
        ]);
});

it('returns 404 when article not found', function () {

    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->getJson(route('user.article.show', 99999));

    $response->assertStatus(404)->assertJson([
        'message' => 'Article with id - 99999 not found',
    ]);
});
