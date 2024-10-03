<?php

declare(strict_types=1);

namespace App\Services\External;

use App\Exceptions\NewYorkTimesServiceException;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final readonly class NewYorkTimes
{
    /**
     * Create a new instance of NewYorkTimes service.
     */
    public function __construct(private string $baseUrl, private string $apiKey)
    {
        //
    }

    /**
     * Fetch articles from data source
     *
     * @throws NewYorkTimesServiceException
     */
    public function getArticles(int $page = 1, ?string $start_date = null, ?string $end_date = null): mixed
    {

        try {

            $start_date ??= Carbon::now()->subHours(25)->toDateString(); //Using 25 hours to cover for 1 hr latency so as not to miss out of syncing any article

            $end_date ??= Carbon::now()->toDateString();

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->get($this->baseUrl.'?api-key='.$this->apiKey.'&page='.$page.'&begin_date='.$start_date.'&end_date='.$end_date.'&sort=newest&fq=source:("The New York Times")');

            if ($response->failed()) {

                $statusCode = $response->status();

                $responseBody = $response->body();

                Log::error("NewYorkTimes.getArticles() - API request failed with status code $statusCode. Response body: $responseBody");

                throw new NewYorkTimesServiceException("API request failed with status code $statusCode");
            }

            return $response->json();

            //
        } catch (ConnectionException) {

            Log::error('NewYorkTimes.getArticles() - Connection timeout');

            throw new NewYorkTimesServiceException('Connection with NewYorkTimes service timeout');
            //
        } catch (NewYorkTimesServiceException $e) {

            Log::error('NewYorkTimes.getArticles() - '.$e->getMessage());

            throw $e;
            //
        } catch (Exception $e) {

            Log::error('NewYorkTimes.getArticles(): Exception - '.$e->getMessage());

            throw $e;
            //
        }
    }
}
