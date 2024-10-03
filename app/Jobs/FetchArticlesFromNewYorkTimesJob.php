<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Article;
use App\Models\ArticleAuthor;
use App\Models\ArticleCategory;
use App\Models\ArticleSource;
use App\Services\External\NewYorkTimes;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class FetchArticlesFromNewYorkTimesJob implements ShouldQueue
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
    public function handle(NewYorkTimes $newYorkTimes): void
    {

        try {

            // Initialize variables for pagination
            $page = 1;
            $articlesPerPage = 10; // Default from New York Times API
            $allArticles = collect();
            $totalResults = 0;
            // The NYT api has a rate limit of 5 calls per minute. So we must account for this. This is the essence of the $maxRequests variable.
            // The value is set to 5 because the max data returned at once by the NYT api is 10. This means we can only get 50 records per job run
            // which is fine as the command is scheduled to run hourly.
            $maxRequests = 5;
            $requestCount = 0; // This is used to keep track and terminate the loop

            do {

                $data = $newYorkTimes->getArticles($page);

                $articlesArray = [];

                if (is_array($data) && isset($data['response']) && is_array($data['response'])) {

                    // Ensure response is OK and data exists
                    if ($data['status'] === 'OK') {

                        /** @var array<int, array<string, mixed>> $articlesArray */
                        $articlesArray = $data['response']['docs'] ?? [];

                        /** @var Collection<int, array<string, mixed>> $articles */
                        $articles = collect($articlesArray);

                        // Merge articles from this page into the main collection
                        $allArticles = $allArticles->merge($articles);

                        // Get the total number of results from the meta data
                        $meta = $data['response']['meta'] ?? [];
                        $totalResults = $meta['hits'] ?? count($articlesArray);

                        // Increment page and limit tracker for the next iteration
                        $page++;
                        $requestCount++;

                    } else {
                        // If the response is not 'OK', break the loop
                        break;
                    }
                }

            } while ($allArticles->count() < $totalResults && count($articlesArray) === $articlesPerPage && $requestCount < $maxRequests);

            // After collecting all articles, proceed to process and store them
            if ($allArticles->isNotEmpty()) {

                $sources = $this->prepareSourcesData($allArticles);

                $authors = $this->prepareAuthorsData($allArticles);

                $categories = $this->prepareCategoriesData($allArticles);

                $processedArticles = $this->prepareArticlesData($allArticles, $sources, $authors, $categories);

                ArticleSource::insertOrIgnore($sources->values()->toArray());

                ArticleCategory::insertOrIgnore($categories->values()->toArray());

                ArticleAuthor::insertOrIgnore($authors->values()->toArray());

                Article::insertOrIgnore($processedArticles->toArray());

            }

        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    /**
     * @param  Collection<int, array{source: string}>  $articles
     * @return Collection<string, array{id: string, name: string, slug: string, created_at: CarbonImmutable, updated_at: CarbonImmutable}>
     */
    private function prepareSourcesData(Collection $articles): Collection
    {
        return $articles->pluck('source')->unique()->map(fn (mixed $name): array => [
            'id' => Str::uuid()->toString(),
            'name' => type($name)->asString(),
            'slug' => Str::slug(type($name)->asString()),
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
        ])->keyBy('name');
    }

    /**
     * @param  Collection<int, array{news_desk: string}>  $articles
     * @return Collection<string, array{id: string, name: string, slug: string, created_at: CarbonImmutable, updated_at: CarbonImmutable}>
     */
    private function prepareCategoriesData(Collection $articles): Collection
    {
        return $articles->pluck('news_desk')->unique()->filter()->map(fn (mixed $name): array => [
            'id' => Str::uuid()->toString(),
            'name' => type($name)->asString(),
            'slug' => Str::slug(type($name)->asString()),
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
        ])->keyBy('name');
    }

    /**
     * @param  Collection<int, array{byline: ?array{person: array<int, array{firstname: string, lastname: string}>}}>  $articles
     * @return Collection<string, array{id: string, name: string, slug: string, created_at: CarbonImmutable, updated_at: CarbonImmutable}>
     */
    private function prepareAuthorsData(Collection $articles): Collection
    {
        return $articles->flatMap(fn (array $article): array => $article['byline']['person'] ?? [])
            ->map(fn (array $person): string => trim($person['firstname'].' '.$person['lastname']))
            ->unique()->filter()
            ->map(fn (mixed $name): array => [
                'id' => Str::uuid()->toString(),
                'name' => $name,
                'slug' => Str::slug($name),
                'created_at' => CarbonImmutable::now(),
                'updated_at' => CarbonImmutable::now(),
            ])->keyBy('name');
    }

    /**
     * @param Collection<int, array{
     *     headline: array{main: string},
     *     abstract: string,
     *     lead_paragraph: string,
     *     web_url: string,
     *     multimedia: array<int, array{type: string, url: string}>,
     *     pub_date: string,
     *     source: string,
     *     byline: array{person: ?array<int, array{firstname: string, lastname: string}>},
     *     news_desk: ?string
     * }> $articles
     * @param  Collection<string, array{id: string, name: string, slug: string, created_at: CarbonImmutable, updated_at: CarbonImmutable}>  $sources
     * @param  Collection<string, array{id: string, name: string, slug: string, created_at: CarbonImmutable, updated_at: CarbonImmutable}>  $authors
     * @param  Collection<string, array{id: string, name: string, slug: string, created_at: CarbonImmutable, updated_at: CarbonImmutable}>  $categories
     * @return Collection<int, array{
     *     id: string,
     *     article_source_id: ?string,
     *     article_author_id: ?string,
     *     article_category_id: ?string,
     *     title: string,
     *     description: ?string,
     *     content: ?string,
     *     article_url: string,
     *     cover_image_url: ?string,
     *     published_at: CarbonImmutable,
     *     created_at: CarbonImmutable,
     *     updated_at: CarbonImmutable
     * }>
     */
    private function prepareArticlesData(Collection $articles, Collection $sources, Collection $authors, Collection $categories): Collection
    {
        return $articles->map(function (array $article) use ($sources, $authors, $categories): array {
            $sourceName = $article['source'];
            $sourceId = $sources[$sourceName]['id'] ?? null;

            $authorName = $this->getAuthorName($article['byline']['person'] ?? []);
            $authorId = $authors[$authorName]['id'] ?? null;

            $categoryName = $article['news_desk'] ?? '';
            $categoryId = $categories[$categoryName]['id'] ?? null;

            $coverImageUrl = collect($article['multimedia'])
                ->firstWhere('type', 'image')['url'] ?? null;

            return [
                'id' => Str::uuid()->toString(),
                'article_source_id' => $sourceId,
                'article_author_id' => $authorId,
                'article_category_id' => $categoryId,
                'title' => $article['headline']['main'],
                'description' => $article['abstract'] !== '' && $article['abstract'] !== '0' ? $article['abstract'] : null,
                'content' => $article['lead_paragraph'] !== '' && $article['lead_paragraph'] !== '0' ? $article['lead_paragraph'] : null,
                'article_url' => $article['web_url'],
                'cover_image_url' => $coverImageUrl ? 'https://www.nytimes.com/'.$coverImageUrl : null,
                'published_at' => CarbonImmutable::parse($article['pub_date']),
                'created_at' => CarbonImmutable::now(),
                'updated_at' => CarbonImmutable::now(),
            ];
        });
    }

    /**
     * @param  array<int, array{firstname: string, lastname: string}>  $persons
     */
    private function getAuthorName(array $persons): string
    {
        if ($persons === []) {
            return '';
        }
        $person = $persons[0];

        return trim($person['firstname'].' '.$person['lastname']);
    }
}
