<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Internal\ArticleAuthorService;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

final class ArticleAuthorController
{
    /**
     * List all article authors.
     */
    public function index(): JsonResponse
    {
        try {

            $cacheKey = 'article_authors';

            $authors = Cache::tags(['authors'])->remember($cacheKey, now()->addMinutes(15), fn (): Collection => (new ArticleAuthorService())->get());

            return response()->json(['message' => 'Article authors retrieved successfully.', 'data' => $authors], 200);

        } catch (Exception $e) {// @codeCoverageIgnoreStart

            Log::error('Error occurred while retrieving article authors: '.$e->getMessage());

            return response()->json(['message' => 'Error occurred while retrieving article authors.'], 500);

        }// @codeCoverageIgnoreEnd
    }
}
