<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Internal\ArticleCategoryService;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

final class ArticleCategoryController
{
    /**
     * List all article categories.
     */
    public function index(): JsonResponse
    {
        try {

            $cacheKey = 'article_categories';

            $categories = Cache::tags(['categories'])->remember($cacheKey, now()->addMinutes(15), fn (): Collection => (new ArticleCategoryService())->get());

            return response()->json(['message' => 'Article categories retrieved successfully.', 'data' => $categories], 200);

        } catch (Exception $e) {// @codeCoverageIgnoreStart

            Log::error('Error occurred while retrieving article categories: '.$e->getMessage());

            return response()->json(['message' => 'Error occurred while retrieving article categories.'], 500);

        }// @codeCoverageIgnoreEnd
    }
}
