<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Jobs\FetchArticlesFromTheGuardianJob;
use App\Models\Article;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

it('successfully fetches articles and stores them in the database', function (): void {

    Http::fake([
        '*' => [
            'response' => [
                'status' => 'ok',
                'total' => 100,
                'results' => [
                    [
                        'sectionName' => 'Technology',
                        'webTitle' => 'New Tech Innovations',
                        'webUrl' => 'https://example.com/new-tech-innovations',
                        'webPublicationDate' => '2024-10-01T12:00:00Z',
                        'fields' => [
                            'body' => 'Content of the article.',
                            'thumbnail' => 'https://example.com/image.jpg',
                        ],
                        'tags' => [
                            [
                                'type' => 'contributor',
                                'webTitle' => 'John Doe',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ]);

    FetchArticlesFromTheGuardianJob::dispatchSync();

    // Assert
    $this->assertDatabaseHas('article_sources', [
        'name' => 'The Guardian',
        'slug' => 'the-guardian',
    ]);

    $this->assertDatabaseHas('article_categories', [
        'name' => 'Technology',
    ]);

    $this->assertDatabaseHas('article_authors', [
        'name' => 'John Doe',
    ]);

    $this->assertDatabaseHas('articles', [
        'title' => 'New Tech Innovations',
        'article_url' => 'https://example.com/new-tech-innovations',
    ]);
});

it('logs an error if the The Guardian API response is not OK', function (): void {

    Http::fake([
        '*' => ['status' => 'ERROR'],
    ]);

    FetchArticlesFromTheGuardianJob::dispatchSync();

    expect(Article::count())->toBe(0);

    Log::assertLogged('error', function ($message): bool {
        return str_contains($message, 'No articles fetched from New York Times API');
    });
});

it('logs an error if an exception is thrown', function (): void {

    Http::fake(function (): never {
        throw new ConnectionException();
    });

    FetchArticlesFromTheGuardianJob::dispatchSync();

    Log::spy();

    Log::shouldHaveReceived('error');
});

it('should be queued', function (): void {

    Queue::fake();

    FetchArticlesFromTheGuardianJob::dispatchSync();

    Queue::assertPushed(FetchArticlesFromTheGuardianJob::class);
});
