<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\FetchArticlesFromNewsApiJob;
use App\Jobs\FetchArticlesFromNewYorkTimesJob;
use App\Jobs\FetchArticlesFromTheGuardianJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

final class FetchArticlesFromSourcesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-articles 
    {source? : The source from which to fetch articles (options: newsapi, theguardian, nytimes)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch latest articles from news api and save locally.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {

        $source = $this->argument('source');

        switch ($source) {
            case 'newsapi':
                FetchArticlesFromNewsApiJob::dispatch();
                $this->info('Fetching articles from News API...');
                $this->invalidateCache();
                break;

            case 'theguardian':
                FetchArticlesFromTheGuardianJob::dispatch();
                $this->info('Fetching articles from The Guardian...');
                $this->invalidateCache();
                break;

            case 'nytimes':
                FetchArticlesFromNewYorkTimesJob::dispatch();
                $this->info('Fetching articles from New York Times...');
                $this->invalidateCache();
                break;

            case null:
                // No argument provided, run all jobs
                FetchArticlesFromNewsApiJob::dispatch();
                FetchArticlesFromTheGuardianJob::dispatch();
                FetchArticlesFromNewYorkTimesJob::dispatch();
                $this->info('Fetching articles from all sources...');
                $this->invalidateCache();
                break;

            default:
                $this->error('Invalid source specified. Valid options are: newsapi, theguardian, nytimes');
        }

    }

    /**
     * Handle cache invalidation
     */
    private function invalidateCache(): void
    {
        Cache::tags(['articles', 'sources', 'authors', 'categories', 'user_articles'])->flush();
        $this->info('Cache invalidated.');
    }
}
