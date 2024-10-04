<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UserArticleSearchRequest;
use App\Models\Article;
use App\Models\User;
use App\Services\Internal\ArticleService;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

final class UserArticleController
{
    /**
     * Create a new instance
     */
    public function __construct(protected ArticleService $articleService) {}

    /**
     * Fetch personalized articles with pagination and search for the logged in user.
     */
    public function index(UserArticleSearchRequest $request): JsonResponse
    {
        try {

            $user = auth()->user();

            $user = type($user)->as(User::class);

            $cacheKey = $this->generateCacheKey($user->id, $request);

            return Cache::tags(['user_articles'])->remember($cacheKey, now()->addMinutes(15), function () use ($user, $request): JsonResponse {

                $user = $user->load(['articleSources', 'articleCategories', 'articleAuthors']);

                $articleSourcesIds = $user->articleSources->pluck('id')->toArray();

                $articleCategoriesIds = $user->articleCategories->pluck('id')->toArray();

                $articleAuthorsIds = $user->articleAuthors->pluck('id')->toArray();

                $query = Article::query();

                $filterLogic = $request->get('filter_logic', 'and');

                $filterLogic = type($filterLogic)->asString();

                // Build dynamic filter based on user preferences and chosen logic
                if (! empty($articleCategoriesIds)) {
                    $this->applyFilterLogic($query, 'article_category_id', $articleCategoriesIds, $filterLogic);
                }

                if (! empty($articleSourcesIds)) {
                    $this->applyFilterLogic($query, 'article_source_id', $articleSourcesIds, $filterLogic);
                }

                if (! empty($articleAuthorsIds)) {
                    $this->applyFilterLogic($query, 'article_author_id', $articleAuthorsIds, $filterLogic);
                }

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
     * Apply dynamic filter logic (AND/OR) for query conditions.
     *
     * @param  Builder<Article>  $query
     * @param  array<mixed>  $values
     * @return Builder<Article>
     */
    private function applyFilterLogic(Builder $query, string $column, array $values, string $logic): Builder
    {
        if ($logic === 'or') {
            return $query->orWhereIn($column, $values);
        }

        // Default to 'and' logic
        return $query->whereIn($column, $values);
    }

    /**
     * Generate a unique cache key
     */
    private function generateCacheKey(int $userId, UserArticleSearchRequest $request): string
    {
        $requestParams = $request->all();
        ksort($requestParams);
        $requestString = http_build_query($requestParams);

        $dataToHash = $userId.$requestString;
        $hashedKey = Hash::make($dataToHash);

        return 'user_articles:'.$hashedKey;
    }
}
