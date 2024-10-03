<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ArticleAuthor;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('returns article authors for authenticated users', function (): void {

    Sanctum::actingAs(User::factory()->create());

    ArticleAuthor::factory()->count(3)->create();

    $response = $this->getJson(route('user.article-author.list'));

    $response->assertStatus(200);

    $response->assertJsonCount(3, 'data');

    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'name', 'created_at', 'updated_at'],
        ],
    ]);
});

it('denies access for unauthenticated users', function (): void {

    $response = $this->getJson(route('user.article-author.list'));

    $response->assertStatus(401);
});
