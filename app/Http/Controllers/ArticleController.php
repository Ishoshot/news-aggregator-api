<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ArticleSearchRequest;
use App\Models\Article;
use App\Services\Internal\ArticleService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

final class ArticleController
{
    /**
     * Create a new instance
     */
    public function __construct(protected ArticleService $articleService) {}

    /**
     * Fetch articles with pagination and search.
     */
    public function index(ArticleSearchRequest $request): JsonResponse
    {
        try {

            $query = Article::query();

            $this->articleService->filter($query, $request);

            $this->articleService->sort($query, $request);

            $articles = $query->paginate($request->integer('per_page', 10));

            return response()->json(['message' => 'Articles retrieved successfully.', 'data' => $articles]);

        } catch (Exception $e) {// @codeCoverageIgnoreStart

            Log::error('Error occurred while retrieving articles: '.$e->getMessage());

            return response()->json(['message' => 'Error occurred while retrieving articles.'], 500);

        }// @codeCoverageIgnoreEnd
    }
}
