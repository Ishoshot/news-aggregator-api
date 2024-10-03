<?php

declare(strict_types=1);

namespace App\Services\External;

use App\Exceptions\TheGuardianServiceException;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final readonly class TheGuardian
{
    /**
     * Create a new instance of TheGuardian service.
     */
    public function __construct(private string $baseUrl, private string $apiKey)
    {
        //
    }

    /**
     * Fetch articles from data source
     *
     * @throws TheGuardianServiceException
     */
    public function getArticles(int $page = 1, ?string $start_date = null, ?string $end_date = null): mixed
    {

        try {

            $start_date ??= Carbon::now()->subHours(25)->toDateString(); //Using 25 hours to cover for 1 hr latency so as not to miss out of syncing any article

            $end_date ??= Carbon::now()->toDateString();

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->get($this->baseUrl.'/search?api-key='.$this->apiKey.'&page='.$page.'&page-size=50&order-by=newest&from-date='.$start_date.'&to-date='.$end_date.'&show-fields=body&show-tags=contributor,type');

            if ($response->failed()) {

                $statusCode = $response->status();

                $responseBody = $response->body();

                Log::error("TheGuardian.getArticles() - API request failed with status code $statusCode. Response body: $responseBody");

                throw new TheGuardianServiceException("API request failed with status code $statusCode");
            }

            return $response->json();

            //
        } catch (ConnectionException) {

            Log::error('TheGuardian.getArticles() - Connection timeout');

            throw new TheGuardianServiceException('Connection with TheGuardian service timeout');
            //
        } catch (TheGuardianServiceException $e) {

            Log::error('TheGuardian.getArticles() - '.$e->getMessage());

            throw $e;
            //
        } catch (Exception $e) {

            Log::error('TheGuardian.getArticles(): Exception - '.$e->getMessage());

            throw $e;
            //
        }
    }
}
