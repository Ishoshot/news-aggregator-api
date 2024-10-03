<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Internal;

use App\Models\ArticleAuthor;
use App\Services\Internal\ArticleAuthorService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $this->service = new ArticleAuthorService();
});

it('creates a new article author', function (): void {

    $data = [
        'name' => 'John Doe',
        'slug' => 'john-doe',
    ];

    $author = $this->service->create($data);

    expect($author)->toBeInstanceOf(ArticleAuthor::class);
    expect($author->name)->toEqual('John Doe');
    expect($author->slug)->toEqual('john-doe');
    expect($author->id)->not()->toBeNull();
});

it('creates multiple article authors', function (): void {

    $data = [
        ['id' => Str::uuid(), 'name' => 'Jane Smith', 'slug' => 'jane-smith'],
        ['id' => Str::uuid(), 'name' => 'Alice Johnson', 'slug' => 'alice-johnson'],
    ];

    $this->service->createMany($data);

    $this->assertCount(2, ArticleAuthor::all());

    expect(ArticleAuthor::where('slug', 'jane-smith')->exists())->toBeTrue();
    expect(ArticleAuthor::where('slug', 'alice-johnson')->exists())->toBeTrue();
});

it('retrieves all article authors', function (): void {

    ArticleAuthor::factory()->create(['name' => 'John Doe']);

    ArticleAuthor::factory()->create(['name' => 'Jane Smith']);

    $authors = $this->service->get();

    expect($authors)->toBeInstanceOf(Collection::class);
    expect($authors->count())->toEqual(2);
    expect($authors->pluck('name')->all())->toEqual(['Jane Smith', 'John Doe']);
});

it('finds an article author by ID', function (): void {

    $author = ArticleAuthor::factory()->create(['name' => 'John Doe']);

    $foundAuthor = $this->service->find($author->id);

    expect($foundAuthor)->toBeInstanceOf(ArticleAuthor::class);
    expect($foundAuthor->id)->toEqual($author->id);

    // Test for a non-existing author
    $notFoundAuthor = $this->service->find('99999');
    expect($notFoundAuthor)->toBeNull();
});
