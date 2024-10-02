<?php

declare(strict_types=1);

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

Route::get('/', function (): JsonResponse {
    return response()->json([
        'app' => 'News Aggregator API',
        'version' => '0.0.1 alpha',
        'disclaimer' => 'This application is a property of Oluwatobi Ishola for Innoscripta AG.',
    ]);
});
