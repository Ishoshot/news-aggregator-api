<?php

declare(strict_types=1);

namespace Tests\Unit\Services\External;

use App\Exceptions\NewYorkTimesServiceException;
use App\Services\External\NewYorkTimes;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

beforeEach(function (): void {
    $this->baseUrl = 'https://api.nytimes.com/svc/search/v2/articlesearch.json';
    $this->apiKey = 'fake-api-key';
    $this->newYorkTimes = new NewYorkTimes($this->baseUrl, $this->apiKey);
});

it('fetches articles successfully', function (): void {

    Http::fake([
        $this->baseUrl.'*' => Http::response([
            'status' => 'OK',
            'response' => ['docs' => [['title' => 'Test Article']]],
        ], 200),
    ]);

    $result = $this->newYorkTimes->getArticles();

    expect($result['response']['docs'][0]['title'])->toBe('Test Article');

});

it('throws NewYorkTimesServiceException on API failure', function (): void {

    Http::fake([
        $this->baseUrl.'*' => Http::response([], 500),
    ]);

    $this->expectException(NewYorkTimesServiceException::class);

    $this->expectExceptionMessage('API request failed with status code 500');

    $this->newYorkTimes->getArticles();
});

it('logs error and throws NewYorkTimesServiceException on connection timeout', function (): void {

    Http::fake(function (): never {
        throw new \Illuminate\Http\Client\ConnectionException();
    });

    Log::spy();

    $this->expectException(NewYorkTimesServiceException::class);

    $this->expectExceptionMessage('Connection with NewYorkTimes service timeout');

    $this->newYorkTimes->getArticles();

    Log::shouldHaveReceived('error')->with('NewYorkTimes.getArticles() - Connection timeout');
});

it('logs and rethrows general exceptions', function (): void {

    Http::fake(function () {
        throw new Exception('Unexpected error occurred');
    });

    Log::spy();

    $this->expectException(Exception::class);

    $this->expectExceptionMessage('Unexpected error occurred');

    $this->newYorkTimes->getArticles();

    Log::shouldHaveReceived('error')->with('NewYorkTimes.getArticles(): Exception - Unexpected error occurred');
});

it('uses default start and end dates when none provided', function (): void {

    Carbon::setTestNow(now());

    Http::fake([
        $this->baseUrl.'*' => Http::response(['status' => 'OK'], 200),
    ]);

    $this->newYorkTimes->getArticles();

    // Get the start date that should have been used (25 hours ago)
    $expectedStartDate = Carbon::now()->subHours(25)->toDateString();

    $expectedEndDate = Carbon::now()->toDateString();

    // Assert that the API was called with the correct start and end dates
    Http::assertSent(function ($request) use ($expectedStartDate, $expectedEndDate): bool {

        // Use rawurldecode() to normalize URL-encoded characters before comparison
        $expectedUrl = "{$this->baseUrl}?api-key={$this->apiKey}&page=1&begin_date={$expectedStartDate}&end_date={$expectedEndDate}&sort=newest&fq=source:(\"The New York Times\")";

        return rawurldecode($request->url()) === rawurldecode($expectedUrl);
    });
});
