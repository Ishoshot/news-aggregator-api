<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Jobs\FetchArticlesFromNewYorkTimesJob;
use App\Models\Article;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

it('fetches articles from New York Times and processes them', function (): void {

    Http::fake([
        '*' => [
            'status' => 'OK',
            'response' => [
                'docs' => [
                    [
                        'headline' => ['main' => 'Sample Article Title'],
                        'abstract' => 'Sample article abstract.',
                        'lead_paragraph' => 'This is the lead paragraph of the article.',
                        'web_url' => 'https://www.nytimes.com/sample-article',
                        'multimedia' => [['type' => 'image', 'url' => '/path/to/image.jpg']],
                        'pub_date' => now()->toISOString(),
                        'source' => 'The New York Times',
                        'byline' => ['person' => [['firstname' => 'John', 'lastname' => 'Doe']]],
                        'news_desk' => 'Arts',
                    ],
                ],
                'meta' => ['hits' => 1],
            ],
        ],
    ]);

    FetchArticlesFromNewYorkTimesJob::dispatchSync();

    expect(Article::count())->toBe(1);

    expect(Article::first()->title)->toBe('Sample Article Title');

    expect(Article::first()->description)->toBe('Sample article abstract.');
});

it('logs an error if the New York Times API response is not OK', function (): void {

    Http::fake([
        '*' => ['status' => 'ERROR'],
    ]);

    FetchArticlesFromNewYorkTimesJob::dispatchSync();

    expect(Article::count())->toBe(0);

    Log::assertLogged('error', function ($message): bool {
        return str_contains($message, 'No articles fetched from New York Times API');
    });
});

it('breaks the loop is response status is not OK', function (): void {

    Http::fake([
        '*' => [
            'status' => 'Mot Ok',
            'response' => [
                'docs' => [
                    [
                        'headline' => ['main' => 'Sample Article Title'],
                        'abstract' => 'Sample article abstract.',
                        'lead_paragraph' => 'This is the lead paragraph of the article.',
                        'web_url' => 'https://www.nytimes.com/sample-article',
                        'multimedia' => [['type' => 'image', 'url' => '/path/to/image.jpg']],
                        'pub_date' => now()->toISOString(),
                        'source' => 'The New York Times',
                        'byline' => ['person' => [['firstname' => 'John', 'lastname' => 'Doe']]],
                        'news_desk' => 'Arts',
                    ],
                ],
                'meta' => ['hits' => 1],
            ],
        ],
    ]);

    FetchArticlesFromNewYorkTimesJob::dispatchSync();

    expect(Article::count())->toBe(0);
});

it('logs an error if an exception is thrown', function (): void {

    Http::fake(function (): never {
        throw new ConnectionException();
    });

    FetchArticlesFromNewYorkTimesJob::dispatchSync();

    Log::spy();

    Log::shouldHaveReceived('error');
});

it('should be queued', function (): void {

    Queue::fake();

    FetchArticlesFromNewYorkTimesJob::dispatchSync();

    Queue::assertPushed(FetchArticlesFromNewYorkTimesJob::class);
});
