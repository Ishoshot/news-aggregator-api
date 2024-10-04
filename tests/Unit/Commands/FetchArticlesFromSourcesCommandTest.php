<?php

declare(strict_types=1);

namespace Tests\Unit\Commands;

use App\Console\Commands\FetchArticlesFromSourcesCommand;
use App\Jobs\FetchArticlesFromNewsApiJob;
use App\Jobs\FetchArticlesFromNewYorkTimesJob;
use App\Jobs\FetchArticlesFromTheGuardianJob;
use Illuminate\Support\Facades\Bus;

it('dispatches the News API job when newsapi is specified as the source', function (): void {

    Bus::fake();

    $this->artisan(FetchArticlesFromSourcesCommand::class, ['source' => 'newsapi'])
        ->expectsOutput('Fetching articles from News API...')
        ->assertExitCode(0);

    Bus::assertDispatched(FetchArticlesFromNewsApiJob::class);
});

it('dispatches the Guardian job when theguardian is specified as the source', function (): void {

    Bus::fake();

    $this->artisan(FetchArticlesFromSourcesCommand::class, ['source' => 'theguardian'])
        ->expectsOutput('Fetching articles from The Guardian...')
        ->assertExitCode(0);

    Bus::assertDispatched(FetchArticlesFromTheGuardianJob::class);

});

it('dispatches the NYTimes job when nytimes is specified as the source', function (): void {

    Bus::fake();

    $this->artisan(FetchArticlesFromSourcesCommand::class, ['source' => 'nytimes'])
        ->expectsOutput('Fetching articles from New York Times...')
        ->assertExitCode(0);

    Bus::assertDispatched(FetchArticlesFromNewYorkTimesJob::class);

});

it('dispatches all jobs when no source is specified', function (): void {

    Bus::fake();

    $this->artisan(FetchArticlesFromSourcesCommand::class)
        ->expectsOutput('Fetching articles from all sources...')
        ->assertExitCode(0);

    Bus::assertDispatched(FetchArticlesFromNewsApiJob::class);

    Bus::assertDispatched(FetchArticlesFromTheGuardianJob::class);

    Bus::assertDispatched(FetchArticlesFromNewYorkTimesJob::class);

});

it('shows an error when an invalid source is specified', function (): void {
    $this->artisan(FetchArticlesFromSourcesCommand::class, ['source' => 'invalidsource'])
        ->expectsOutput('Invalid source specified. Valid options are: newsapi, theguardian, nytimes')
        ->assertExitCode(0);
});
