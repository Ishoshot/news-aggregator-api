<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Jobs\FetchArticlesFromNewsApiJob;
use App\Models\Article;
use App\Models\ArticleAuthor;
use App\Models\ArticleSource;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

it('fetches articles from the News API and stores them in the database', function () {

    Http::fake([
        '*' => [
            'status' => 'ok',
            'totalResults' => 2,
            'articles' => [
                [
                    'title' => 'Article 1',
                    'description' => 'Description 1',
                    'content' => 'Content 1',
                    'url' => 'http://example.com/article1',
                    'urlToImage' => 'http://example.com/image1',
                    'publishedAt' => '2024-10-01T12:00:00Z',
                    'source' => ['name' => 'Source 1'],
                    'author' => 'Author 1',
                ],
                [
                    'title' => 'Article 2',
                    'description' => 'Description 2',
                    'content' => 'Content 2',
                    'url' => 'http://example.com/article2',
                    'urlToImage' => 'http://example.com/image2',
                    'publishedAt' => '2024-10-01T12:00:00Z',
                    'source' => ['name' => 'Source 2'],
                    'author' => 'Author 2',
                ],
            ],
        ],
    ]);

    FetchArticlesFromNewsApiJob::dispatch();

    expect(Article::count())->toEqual(2);

    expect(ArticleSource::count())->toEqual(2);

    expect(ArticleAuthor::count())->toEqual(2);

    $article = Article::first();
    expect($article->title)->toEqual('Article 1');
    expect($article->description)->toEqual('Description 1');
    expect($article->article_url)->toEqual('http://example.com/article1');
    expect($article->published_at)->toEqual(CarbonImmutable::parse('2024-10-01T12:00:00Z'));

    $source = ArticleSource::first();
    expect($source->name)->toEqual('Source 1');

    $author = ArticleAuthor::first();
    expect($author->name)->toEqual('Author 1');
});

it('logs an error if the News API returns an error', function () {

    Http::fake([
        '*' => ['status' => 'error', 'message' => 'Error message'],
    ]);

    FetchArticlesFromNewsApiJob::dispatch();

    expect(Article::count())->toEqual(0);

    expect(ArticleSource::count())->toEqual(0);

    expect(ArticleAuthor::count())->toEqual(0);
});

it('logs an error if an exception is thrown', function (): void {

    Http::fake(function (): never {
        throw new ConnectionException();
    });

    FetchArticlesFromNewsApiJob::dispatchSync();

    Log::spy();

    Log::shouldHaveReceived('error');
});

it('should be queued', function (): void {

    Queue::fake();

    FetchArticlesFromNewsApiJob::dispatchSync();

    Queue::assertPushed(FetchArticlesFromNewsApiJob::class);
});
