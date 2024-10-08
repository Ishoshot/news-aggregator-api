<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ArticleSearchRequest;
use App\Models\Article;
use App\Models\User;
use App\Services\Internal\ArticleService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
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

            $user = auth()->user();

            $user = type($user)->as(User::class);

            $cacheKey = $this->generateCacheKey($user->id, $request);

            return Cache::tags(['articles'])->remember($cacheKey, now()->addMinutes(15), function () use ($request): JsonResponse {

                $query = Article::query();

                $this->articleService->filter($query, $request);

                $this->articleService->sort($query, $request);

                $articles = $query->paginate($request->integer('per_page', 10));

                return response()->json(['message' => 'Articles retrieved successfully.', 'data' => $articles]);
            });

        } catch (Exception $e) {// @codeCoverageIgnoreStart

            Log::error('Error occurred while retrieving articles: '.$e->getMessage());

            return response()->json(['message' => 'Error occurred while retrieving articles.'], 500);

        }// @codeCoverageIgnoreEnd
    }

    /**
     * Retrieve a single article's details.
     */
    public function show(string $id): JsonResponse
    {
        try {

            $article = Article::with([
                'source:id,name,slug',
                'author:id,name,slug',
                'category:id,name,slug',
            ])->findOrFail($id);

            return response()->json(['message' => 'Article details retrieved successfully.', 'data' => $article]);

        } catch (ModelNotFoundException $e) {

            Log::error('Article with id - '.$id.' not found: '.$e->getMessage());

            return response()->json(['message' => 'Article with id - '.$id.' not found'], 404);

        } catch (Exception $e) {// @codeCoverageIgnoreStart

            Log::error('Error occurred while retrieving articles: '.$e->getMessage());

            return response()->json(['message' => 'Error occurred while retrieving article.'], 500);

        }// @codeCoverageIgnoreEnd
    }

    /**
     * Generate a unique cache key
     */
    private function generateCacheKey(int $userId, ArticleSearchRequest $request): string
    {
        $requestParams = $request->all();
        ksort($requestParams);
        $requestString = http_build_query($requestParams);

        $dataToHash = $userId.$requestString;
        $hashedKey = Hash::make($dataToHash);

        return 'articles:'.$hashedKey;
    }
}
