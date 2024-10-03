<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\FetchArticlesFromNewsApiJob;
use Illuminate\Console\Command;

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
                break;

            case 'theguardian':
                // FetchArticlesFromTheGuardianJob::dispatch();
                $this->info('Fetching articles from The Guardian...');
                break;

            case 'nytimes':
                // FetchArticlesFromNewYorkTimesJob::dispatch();
                $this->info('Fetching articles from New York Times...');
                break;

            case null:
                // No argument provided, run all jobs
                FetchArticlesFromNewsApiJob::dispatch();
                // FetchArticlesFromTheGuardianJob::dispatch();
                // FetchArticlesFromNewYorkTimesJob::dispatch();
                $this->info('Fetching articles from all sources...');
                break;

            default:
                $this->error('Invalid source specified. Valid options are: newsapi, theguardian, nytimes');
        }

    }
}
