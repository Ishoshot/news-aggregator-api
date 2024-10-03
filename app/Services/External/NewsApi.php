<?php

declare(strict_types=1);

namespace App\Services\External;

use App\Exceptions\NewsApiServiceException;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final readonly class NewsApi
{
    /**
     * Create a new instance of NewsApi service.
     */
    public function __construct(private string $baseUrl, private string $apiKey)
    {
        //
    }

    /**
     * Fetch articles from data source
     *
     * @throws NewsApiServiceException
     */
    public function getArticles(int $page = 1, ?string $start_date = null, ?string $end_date = null): mixed
    {

        try {

            $start_date ??= Carbon::now()->subHours(25)->toDateString(); //Using 25 hours to cover for 1 hr latency so as not to miss out of syncing any article

            $end_date ??= Carbon::now()->toDateString();

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.$this->apiKey,
            ])->get($this->baseUrl.'/everything?domains=bbc.co.uk,techcrunch.com,engadget.com&page='.$page.'&sortBy=publishedAt&from='.$start_date.'&to='.$end_date);

            if ($response->failed()) {

                $statusCode = $response->status();

                $responseBody = $response->body();

                Log::error("NewsApi.getArticles() - API request failed with status code $statusCode. Response body: $responseBody");

                throw new NewsApiServiceException("API request failed with status code $statusCode");
            }

            return $response->json();

            //
        } catch (ConnectionException) {

            Log::error('NewsApi.getArticles() - Connection timeout');

            throw new NewsApiServiceException('Connection with NewsApi service timeout');
            //
        } catch (NewsApiServiceException $e) {

            Log::error('NewsApi.getArticles() - '.$e->getMessage());

            throw $e;
            //
        } catch (Exception $e) {

            Log::error('NewsApi.getArticles(): Exception - '.$e->getMessage());

            throw $e;
            //
        }
    }
}
