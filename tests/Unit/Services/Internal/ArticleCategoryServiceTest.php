<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Internal;

use App\Models\ArticleCategory;
use App\Services\Internal\ArticleCategoryService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $this->service = new ArticleCategoryService();
});

it('creates a new article category', function (): void {

    $data = [
        'name' => 'Technology',
        'slug' => 'technology',
    ];

    $category = $this->service->create($data);

    expect($category)->toBeInstanceOf(ArticleCategory::class);
    expect($category->name)->toEqual('Technology');
    expect($category->slug)->toEqual('technology');
    expect($category->id)->not()->toBeNull();
});

it('creates multiple article categories', function (): void {

    $data = [
        ['id' => Str::uuid(), 'name' => 'Health', 'slug' => 'health'],
        ['id' => Str::uuid(), 'name' => 'Science', 'slug' => 'science'],
    ];

    $this->service->createMany($data);

    $this->assertCount(2, ArticleCategory::all());
    expect(ArticleCategory::where('slug', 'health')->exists())->toBeTrue();
    expect(ArticleCategory::where('slug', 'science')->exists())->toBeTrue();
});

it('retrieves all article categories', function (): void {

    ArticleCategory::factory()->create(['name' => 'Sports']);
    ArticleCategory::factory()->create(['name' => 'Technology']);

    $categories = $this->service->get();

    expect($categories)->toBeInstanceOf(Collection::class);
    expect($categories->count())->toEqual(2);
    expect($categories->pluck('name')->all())->toEqual(['Sports', 'Technology']);
});

it('finds an article category by ID', function (): void {

    $category = ArticleCategory::factory()->create(['name' => 'Entertainment']);

    $foundCategory = $this->service->find($category->id);

    expect($foundCategory)->toBeInstanceOf(ArticleCategory::class);
    expect($foundCategory->id)->toEqual($category->id);

    $notFoundCategory = $this->service->find('99999');
    expect($notFoundCategory)->toBeNull();
});
