<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Article;
use App\Models\ArticleAuthor;
use App\Models\ArticleCategory;
use App\Models\ArticleSource;
use App\Services\External\TheGuardian;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class FetchArticlesFromTheGuardianJob implements ShouldQueue
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
    public function handle(TheGuardian $theGuardian): void
    {

        // We want to get the data returned from the api. Most times, the number of items returned is greater than the maximum number
        // of items that can be returned at once (50), so we need to traverse through the pages to ensure we get all the data there is.

        try {

            // Initialize page-related variables for traversing through multiple pages
            $page = 1;
            $articlesPerPage = 50; // Set per-page limit for The Guardian API
            $allArticles = collect();
            $articlesArray = [];
            $totalResults = 0;

            $source = [
                'id' => Str::uuid()->toString(),
                'name' => 'The Guardian',
                'slug' => 'the-guardian',
                'created_at' => CarbonImmutable::now(),
                'updated_at' => CarbonImmutable::now(),
            ];

            // Prepare sources data (insert only once since it's the same source)
            ArticleSource::insertOrIgnore([$source]);

            do {

                $data = $theGuardian->getArticles($page);

                if (is_array($data) && isset($data['response']) && is_array($data['response'])) {

                    $status = $data['response']['status'] ?? null;

                    // Ensure status is 'ok' and articles exist
                    if ($status === 'ok') {

                        /** @var array<int, array<string, mixed>> $articlesArray */
                        $articlesArray = $data['response']['results'] ?? [];

                        /** @var Collection<int, array<string, mixed>> $articles */
                        $articles = collect($articlesArray);

                        // Append the fetched articles to the main collection
                        $allArticles = $allArticles->merge($articles);

                        // Get total results and adjust the number of pages needed
                        $totalResults = $data['response']['total'] ?? count($articles);

                        $page++;
                    }
                }
            } while ($allArticles->count() < $totalResults && count($articlesArray) === $articlesPerPage);

            // Process the collected articles if any exist
            if ($allArticles->isNotEmpty()) {

                /** @var Collection<string, array{id: string, name: string, slug: string, created_at: CarbonImmutable, updated_at: CarbonImmutable}> $categories */
                $categories = $this->prepareCategoriesData($allArticles);

                /** @var Collection<string, array{id: string, name: string, slug: string, created_at: CarbonImmutable, updated_at: CarbonImmutable}> $authors */
                $authors = $this->prepareAuthorsData($allArticles);

                /** @var Collection<int, array<string, mixed>> $processedArticles */
                $processedArticles = $this->prepareArticlesData($allArticles, $categories, $authors, $source);

                // Insert categories, authors, and articles
                ArticleCategory::insertOrIgnore($categories->values()->toArray());
                ArticleAuthor::insertOrIgnore($authors->values()->toArray());
                Article::insertOrIgnore($processedArticles->toArray());
            }

        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    /**
     * Prepare the category data from The Guardian articles.
     *
     * @param  Collection<int, array<string, mixed>>  $articles
     * @return Collection<string, array{id: string, name: string, slug: string, created_at: CarbonImmutable, updated_at: CarbonImmutable}>
     */
    private function prepareCategoriesData(Collection $articles): Collection
    {
        return $articles->pluck('sectionName')->unique()
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
     * Prepare the author data from The Guardian articles.
     *
     * @param  Collection<int, array<string, mixed>>  $articles
     * @return Collection<string, array{id: string, name: string, slug: string, created_at: CarbonImmutable, updated_at: CarbonImmutable}>
     */
    private function prepareAuthorsData(Collection $articles): Collection
    {
        return $articles->pluck('tags')
            ->flatten(1)
            ->filter(fn ($tag): bool => is_array($tag) && $tag['type'] === 'contributor' && ! empty($tag['webTitle']))
            ->pluck('webTitle')
            ->unique()
            ->map(fn ($name): array => [
                'id' => Str::uuid()->toString(),
                'name' => type($name)->asString(),
                'slug' => Str::slug(type($name)->asString()),
                'created_at' => now(),
                'updated_at' => now(),
            ])->keyBy('name');
    }

    /**
     * Prepare the article data from The Guardian API response.
     *
     * @param  Collection<int, array{sectionName: ?string, webTitle: ?string, webUrl: ?string, webPublicationDate: ?string,
     *     fields: array{trailText: ?string, body: ?string, thumbnail: ?string},
     *     tags: array<int, array{type: string, webTitle: string}>
     * }>  $articles
     * @param  Collection<string, array{id: string, name: string, slug: string, created_at: CarbonImmutable, updated_at: CarbonImmutable}>  $categories
     * @param  Collection<string, array{id: string, name: string, slug: string, created_at: CarbonImmutable, updated_at: CarbonImmutable}>  $authors
     * @param  array{id: string, name: string, slug: string, created_at: CarbonImmutable, updated_at: CarbonImmutable}  $source
     * @return Collection<int, array{id: string, article_source_id: string, article_author_id: ?string, article_category_id: ?string, title: ?string, description: ?string, content: ?string, article_url: ?string, cover_image_url: ?string, published_at: CarbonImmutable, created_at: CarbonImmutable, updated_at: CarbonImmutable}>
     */
    private function prepareArticlesData(Collection $articles, Collection $categories, Collection $authors, array $source): Collection
    {
        return $articles->map(function (mixed $article) use ($categories, $authors, $source): array {

            $categoryName = $article['sectionName'] ?? null;
            $categoryId = $categoryName && isset($categories[$categoryName]) ? $categories[$categoryName]['id'] : null;

            $authorName = collect($article['tags'])->firstWhere('type', 'contributor')['webTitle'] ?? null;
            $authorId = $authorName && isset($authors[$authorName]) ? $authors[$authorName]['id'] : null;

            return [
                'id' => Str::uuid()->toString(),
                'article_source_id' => $source['id'],
                'article_author_id' => $authorId,
                'article_category_id' => $categoryId,
                'title' => $article['webTitle'] ?? null,
                'description' => $article['fields']['trailText'] ?? null,
                'content' => $article['fields']['body'] ?? null,
                'article_url' => $article['webUrl'] ?? null,
                'cover_image_url' => $article['fields']['thumbnail'] ?? null,
                'published_at' => CarbonImmutable::parse(type($article['webPublicationDate'])->asString()),
                'created_at' => CarbonImmutable::now(),
                'updated_at' => CarbonImmutable::now(),
            ];
        });
    }
}
