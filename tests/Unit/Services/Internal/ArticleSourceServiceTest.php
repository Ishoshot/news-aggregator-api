<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Internal;

use App\Models\ArticleSource;
use App\Services\Internal\ArticleSourceService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $this->service = new ArticleSourceService();
});

test('it can create a new article source', function (): void {

    $data = [
        'name' => 'TechCrunch',
        'slug' => 'techcrunch',
    ];

    $articleSource = $this->service->create($data);

    expect($articleSource)->toBeInstanceOf(ArticleSource::class);
    expect($articleSource->name)->toBe('TechCrunch');
    expect($articleSource->slug)->toBe('techcrunch');
});

test('it can create multiple article sources', function (): void {

    $data = [
        ['id' => Str::uuid(), 'name' => 'BBC News', 'slug' => 'bbc-news'],
        ['id' => Str::uuid(), 'name' => 'CNN', 'slug' => 'cnn'],
    ];

    $this->service->createMany($data);

    $sources = ArticleSource::all();

    expect($sources)->toHaveCount(2);
    expect($sources->pluck('name')->toArray())->toEqual(['BBC News', 'CNN']);
});

test('it can get all article sources', function (): void {

    ArticleSource::factory()->create(['name' => 'TechCrunch']);
    ArticleSource::factory()->create(['name' => 'BBC News']);

    $sources = $this->service->get();

    expect($sources)->toBeInstanceOf(Collection::class);
    expect($sources)->toHaveCount(2);
});

test('it can find a specific article source by ID', function (): void {

    $source = ArticleSource::factory()->create(['name' => 'TechCrunch']);

    $foundSource = $this->service->find($source->id);

    expect($foundSource)->toBeInstanceOf(ArticleSource::class);
    expect($foundSource->name)->toBe('TechCrunch');
});

test('it returns null when the article source is not found', function (): void {

    $foundSource = $this->service->find('non-existent-id');

    expect($foundSource)->toBeNull();
});
