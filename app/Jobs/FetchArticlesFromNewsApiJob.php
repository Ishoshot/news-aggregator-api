<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Article;
use App\Models\ArticleAuthor;
use App\Models\ArticleSource;
use App\Services\External\NewsApi;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class FetchArticlesFromNewsApiJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(NewsApi $newsApi): void
    {
        // We want to get the data returned from the api. Most times, the number of items returned is greater than the maximum number
        // of items that can be returned at once (100), so we need to traverse through the pages to ensure we get all the data there is.

        try {

            // Initialize variables
            $page = 1;
            $articlesPerPage = 100;
            $allArticles = collect();
            $articlesArray = [];
            $totalResults = 0;

            do {

                /** @var array<string, mixed> $data */
                $data = $newsApi->getArticles($page);

                // Ensure status is 'ok' and data exists
                if ($data['status'] === 'ok') {

                    /** @var array<int, array<string, mixed>> $articlesArray */
                    $articlesArray = $data['articles'];

                    /** @var Collection<int, array<string, mixed>> $articles */
                    $articles = collect($articlesArray);

                    // Append articles from this page to the total collection
                    $allArticles = $allArticles->merge($articles);

                    // Check total results and calculate the number of pages required
                    $totalResults = $data['totalResults'] ?? count($articles);

                    $page++;
                }
            } while ($allArticles->count() < $totalResults && count($articlesArray) === $articlesPerPage);

            // Here, we have traversed through all the possible pages
            if ($allArticles->isNotEmpty()) {

                /** @var Collection<string, array{id: string, name: string, slug: string, created_at: \Carbon\CarbonImmutable, updated_at: \Carbon\CarbonImmutable}> $sources */
                $sources = $this->prepareSourcesData($allArticles);

                /** @var Collection<string, array{id: string, name: string, slug: string, created_at: \Carbon\CarbonImmutable, updated_at: \Carbon\CarbonImmutable}> $authors */
                $authors = $this->prepareAuthorsData($allArticles);

                /** @var Collection<int, array<string, mixed>> $processedArticles */
                $processedArticles = $this->prepareArticlesData($allArticles, $sources, $authors);

                ArticleSource::insertOrIgnore($sources->values()->toArray());

                ArticleAuthor::insertOrIgnore($authors->values()->toArray());

                Article::insertOrIgnore($processedArticles->toArray());

            }

        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    /**
     * Prepare the source data from the articles.
     *
     * @param  Collection<int, array<string, mixed>>  $articles
     * @return Collection<string, array{id: string, name: string, slug: string, created_at: \Carbon\CarbonImmutable, updated_at: \Carbon\CarbonImmutable}>
     */
    private function prepareSourcesData(Collection $articles): Collection
    {
        return $articles->pluck('source.name')->unique()
            ->filter(fn ($name): bool => is_string($name) && $name !== '')
            ->map(fn (string $name): array => [
                'id' => Str::uuid()->toString(),
                'name' => $name,
                'slug' => Str::slug($name),
                'created_at' => now(),
                'updated_at' => now(),
            ])->keyBy('name');
    }

    /**
     * Prepare the author data from the articles.
     *
     * @param  Collection<int, array<string, mixed>>  $articles
     * @return Collection<string, array{id: string, name: string, slug: string, created_at: \Carbon\CarbonImmutable, updated_at: \Carbon\CarbonImmutable}>
     */
    private function prepareAuthorsData(Collection $articles): Collection
    {
        return $articles->pluck('author')->unique()
            ->filter(fn ($name): bool => is_string($name) && $name !== '')
            ->map(fn (string $name): array => [
                'id' => Str::uuid()->toString(),
                'name' => $name,
                'slug' => Str::slug($name),
                'created_at' => now(),
                'updated_at' => now(),
            ])->keyBy('name');
    }

    /**
     * Prepare the article data from the API response.
     *
     * @param  Collection<int, array<string, mixed>>  $articles
     * @param  Collection<string, array{id: string, name: string, slug: string, created_at: \Carbon\CarbonImmutable, updated_at: \Carbon\CarbonImmutable}>  $sources
     * @param  Collection<string, array{id: string, name: string, slug: string, created_at: \Carbon\CarbonImmutable, updated_at: \Carbon\CarbonImmutable}>  $authors
     * @return Collection<int, array{id: string, article_source_id: string|null, article_author_id: string|null, article_category_id: null, title: mixed, description: mixed, content: mixed, article_url: mixed, cover_image_url: mixed, published_at: \Carbon\CarbonImmutable, created_at: \Carbon\CarbonImmutable, updated_at: \Carbon\CarbonImmutable}>
     */
    private function prepareArticlesData(Collection $articles, Collection $sources, Collection $authors): Collection
    {
        return $articles->filter(fn (array $article): bool => $article['title'] !== '[Removed]' && $article['description'] !== '[Removed]' && $article['content'] !== '[Removed]')
            ->map(function (array $article) use ($sources, $authors): array {

                $sourceName = is_array($article['source'] ?? null) ? ($article['source']['name'] ?? null) : null;
                $sourceId = $sourceName && isset($sources[$sourceName]) ? $sources[$sourceName]['id'] : null;

                $authorId = isset($authors[$article['author']]) ? $authors[$article['author']]['id'] : null;

                return [
                    'id' => Str::uuid()->toString(),
                    'article_source_id' => $sourceId,
                    'article_author_id' => $authorId,
                    'article_category_id' => null,
                    'title' => $article['title'],
                    'description' => $article['description'],
                    'content' => $article['content'],
                    'article_url' => $article['url'],
                    'cover_image_url' => $article['urlToImage'],
                    'published_at' => CarbonImmutable::parse(type($article['publishedAt'])->asString()),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            });
    }
}
