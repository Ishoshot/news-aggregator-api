<?php

declare(strict_types=1);

namespace App\Services\Internal;

use App\Models\ArticleCategory;
use Illuminate\Database\Eloquent\Collection;

final readonly class ArticleCategoryService
{
    /**
     * Create a new service instance
     */
    public function __construct()
    {
        //
    }

    /**
     * Create a new article category.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): ArticleCategory
    {
        return ArticleCategory::create($data);
    }

    /**
     * Create multiple article categories.
     *
     * @param  array<int, array<string, mixed>>  $data
     */
    public function createMany(array $data): void
    {
        ArticleCategory::insert($data);
    }

    /**
     * Get all article categories.
     *
     * @return Collection<int, ArticleCategory>
     */
    public function get(): Collection
    {
        return ArticleCategory::orderBy('name', 'asc')->get();
    }

    /**
     * Find a specific article category by ID.
     */
    public function find(string $id): ?ArticleCategory
    {
        return ArticleCategory::find($id);
    }
}
