<?php

declare(strict_types=1);

namespace Tests\Unit\Services\External;

use App\Exceptions\TheGuardianServiceException;
use App\Services\External\TheGuardian;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

beforeEach(function (): void {
    $this->baseUrl = 'https://content.guardianapis.com';
    $this->apiKey = 'fake-api-key';
    $this->guardianService = new TheGuardian($this->baseUrl, $this->apiKey);
});

it('fetches articles successfully from the API', function (): void {

    Http::fake([
        "{$this->baseUrl}*" => Http::response(['response' => ['status' => 'ok', 'results' => []]], 200),
    ]);

    $response = $this->guardianService->getArticles();

    expect($response)->toBeArray()->toHaveKey('response');

    Http::assertSent(function ($request): bool {
        return $request->hasHeader('Content-Type', 'application/json') &&
               $request->url() === "{$this->baseUrl}/search?api-key={$this->apiKey}&page=1&page-size=50&order-by=newest&from-date=".Carbon::now()->subHours(25)->toDateString().'&to-date='.Carbon::now()->toDateString().'&show-fields=body&show-tags=contributor,type';
    });

});

it('throws an exception when the API request fails', function (): void {

    Http::fake([
        "{$this->baseUrl}*" => Http::response(null, 500),
    ]);

    Log::spy();

    expect(fn (): mixed => $this->guardianService->getArticles())->toThrow(TheGuardianServiceException::class);

    Log::shouldHaveReceived('error');

});

it('handles connection timeout gracefully', function (): void {

    Http::fake(function (): never {
        throw new ConnectionException();
    });

    Log::spy();

    expect(fn () => $this->guardianService->getArticles())->toThrow(TheGuardianServiceException::class);

    Log::shouldHaveReceived('error')->with('TheGuardian.getArticles() - Connection timeout');

});

it('uses default start and end dates when none are provided', function (): void {

    Carbon::setTestNow(now());

    Http::fake([
        "{$this->baseUrl}*" => Http::response(['response' => ['status' => 'ok']], 200),
    ]);

    $this->guardianService->getArticles();

    $expectedStartDate = Carbon::now()->subHours(25)->toDateString();

    $expectedEndDate = Carbon::now()->toDateString();

    Http::assertSent(function ($request) use ($expectedStartDate, $expectedEndDate): bool {
        return $request->url() === "{$this->baseUrl}/search?api-key={$this->apiKey}&page=1&page-size=50&order-by=newest&from-date={$expectedStartDate}&to-date={$expectedEndDate}&show-fields=body&show-tags=contributor,type";
    });

});
