<?php

declare(strict_types=1);

namespace App\Services\Internal;

use App\Models\Article;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final readonly class ArticleService
{
    /**
     * Create a new service instance
     */
    public function __construct()
    {
        //
    }

    /**
     * Create a new article
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Article
    {
        return Article::create($data);
    }

    /**
     * Apply filtering to the query based on the request parameters.
     *
     * @param  Builder<Article>  $query
     */
    public function filter(Builder $query, Request $request): void
    {
        // Keyword search
        if ($request->filled('keyword')) {
            $query->where(function (Builder $subQuery) use ($request): void {
                $subQuery->where('title', 'like', '%'.$request->keyword.'%') // @phpstan-ignore-line
                    ->orWhere('description', 'like', '%'.$request->keyword.'%'); // @phpstan-ignore-line
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $categories = explode(',', type($request->category)->asString());
            $query->whereIn('article_category_id', $categories);
        }

        // Source filter
        if ($request->filled('source')) {
            $sources = explode(',', type($request->source)->asString());
            $query->whereIn('article_source_id', $sources);
        }

        // Author filter
        if ($request->filled('author')) {
            $authors = explode(',', type($request->author)->asString());
            $query->whereIn('article_author_id', $authors);
        }

        // Exact date filter
        if ($request->filled('date')) {
            $date = Carbon::parse(type($request->date)->asString());
            $query->whereDate('published_at', $date);
        }

        // Start date filter
        if ($request->filled('start_date')) {
            $date = Carbon::parse(type($request->start_date)->asString());
            $query->where('published_at', '>=', $date);
        }

        // End date filter
        if ($request->filled('end_date')) {
            $date = Carbon::parse(type($request->end_date)->asString());
            $query->where('published_at', '<=', $date);
        }

    }

    /**
     * Apply sorting to the query based on the request parameters.
     *
     * @param  Builder<Article>  $query
     */
    public function sort(Builder $query, Request $request): void
    {
        if ($request->filled('order')) {
            $order = $request->order === 'desc' ? 'desc' : 'asc';
            $query->orderBy('published_at', $order);
        }
    }
}
