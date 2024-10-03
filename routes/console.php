<?php

declare(strict_types=1);

use App\Console\Commands\FetchArticlesFromSourcesCommand;

Schedule::command(FetchArticlesFromSourcesCommand::class)->hourly();
