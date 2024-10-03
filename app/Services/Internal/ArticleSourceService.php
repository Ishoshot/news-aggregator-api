<?php

declare(strict_types=1);

namespace App\Services\Internal;

use App\Models\ArticleSource;
use Illuminate\Database\Eloquent\Collection;

final readonly class ArticleSourceService
{
    /**
     * Create a new service instance
     */
    public function __construct()
    {
        //
    }

    /**
     * Create a new article source.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): ArticleSource
    {
        return ArticleSource::create($data);
    }

    /**
     * Create multiple article sources.
     *
     * @param  array<int, array<string, mixed>>  $data
     */
    public function createMany(array $data): void
    {
        ArticleSource::insert($data);
    }

    /**
     * Get all article sources.
     *
     * @return Collection<int, ArticleSource>
     */
    public function get(): Collection
    {
        return ArticleSource::orderBy('name', 'asc')->get();
    }

    /**
     * Find a specific article source by ID.
     */
    public function find(string $id): ?ArticleSource
    {
        return ArticleSource::find($id);
    }
}
