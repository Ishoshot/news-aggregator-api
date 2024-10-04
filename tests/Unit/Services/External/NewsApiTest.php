<?php

declare(strict_types=1);

namespace Tests\Unit\Services\External;

use App\Exceptions\NewsApiServiceException;
use App\Services\External\NewsApi;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

beforeEach(function (): void {
    $this->baseUrl = 'https://newsapi.org/v2';
    $this->apiKey = 'fake-api-key';
    $this->newsApi = new NewsApi($this->baseUrl, $this->apiKey);
});

it('fetches articles successfully from the API', function (): void {

    Http::fake([
        "{$this->baseUrl}*" => Http::response(['status' => 'ok', 'articles' => []], 200),
    ]);

    $response = $this->newsApi->getArticles();

    expect($response)->toBeArray()->toHaveKey('status', 'ok');

    Http::assertSent(function ($request) {
        return $request->hasHeader('Authorization', 'Bearer '.$this->apiKey);
    });

});

it('throws an exception when the API request fails', function (): void {

    Http::fake([
        "{$this->baseUrl}*" => Http::response(null, 500),
    ]);

    $this->expectException(NewsApiServiceException::class);

    $this->expectExceptionMessage('API request failed with status code 500');

    $this->newsApi->getArticles();

});

it('logs and rethrows general exceptions', function (): void {

    Http::fake(function () {
        throw new Exception('Unexpected error occurred');
    });

    Log::spy();

    $this->expectException(Exception::class);

    $this->expectExceptionMessage('Unexpected error occurred');

    $this->newsApi->getArticles();

    Log::shouldHaveReceived('error')->with('NewsApi.getArticles(): Exception - Unexpected error occurred');
});

it('handles connection timeout gracefully', function (): void {

    Http::fake(function (): never {
        throw new ConnectionException();
    });

    Log::spy();

    expect(fn (): mixed => $this->newsApi->getArticles())->toThrow(NewsApiServiceException::class);

    Log::shouldHaveReceived('error')->with('NewsApi.getArticles() - Connection timeout');

});

it('uses default start and end dates when none are provided', function (): void {

    Carbon::setTestNow(now());

    Http::fake([
        "{$this->baseUrl}*" => Http::response(['status' => 'ok'], 200),
    ]);

    $this->newsApi->getArticles();

    // Get the start date that should have been used (25 hours ago)
    $expectedStartDate = Carbon::now()->subHours(25)->toDateString();
    $expectedEndDate = Carbon::now()->toDateString();

    // Assert that the API was called with the correct start and end dates
    Http::assertSent(function ($request) use ($expectedStartDate, $expectedEndDate): bool {
        return $request->url() === "{$this->baseUrl}/everything?domains=bbc.co.uk,techcrunch.com,engadget.com&page=1&sortBy=publishedAt&from={$expectedStartDate}&to={$expectedEndDate}";
    });
});
