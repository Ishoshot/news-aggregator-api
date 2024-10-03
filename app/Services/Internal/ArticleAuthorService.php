<?php

declare(strict_types=1);

namespace App\Services\Internal;

use App\Models\ArticleAuthor;
use Illuminate\Database\Eloquent\Collection;

final readonly class ArticleAuthorService
{
    /**
     * Create a new service instance
     */
    public function __construct()
    {
        //
    }

    /**
     * Create a new article author.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): ArticleAuthor
    {
        return ArticleAuthor::create($data);
    }

    /**
     * Create multiple article authors.
     *
     * @param  array<int, array<string, mixed>>  $data
     */
    public function createMany(array $data): void
    {
        ArticleAuthor::insert($data);
    }

    /**
     * Get all article authors.
     *
     * @return Collection<int, ArticleAuthor>
     */
    public function get(): Collection
    {
        return ArticleAuthor::orderBy('name', 'asc')->get();
    }

    /**
     * Find a specific article author by ID.
     */
    public function find(string $id): ?ArticleAuthor
    {
        return ArticleAuthor::find($id);
    }
}
