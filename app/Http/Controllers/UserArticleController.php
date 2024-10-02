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

            $user = $user->load(['articleSources', 'articleCategories', 'articleAuthors']);

            $articleSourcesIds = $user->articleSources->pluck('id')->toArray();

            $articleCategoriesIds = $user->articleCategories->pluck('id')->toArray();

            $articleAuthorsIds = $user->articleAuthors->pluck('id')->toArray();

            $query = Article::query();

            $filterLogic = $request->get('filter_logic', 'and');

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

        } catch (Exception $e) {// @codeCoverageIgnoreStart

            Log::error('Error occurred while retrieving articles: '.$e->getMessage());

            return response()->json(['message' => 'Error occurred while retrieving articles.'], 500);

        }// @codeCoverageIgnoreEnd
    }

    /**
     * Apply dynamic filter logic (AND/OR) for query conditions.
     */
    private function applyFilterLogic(Builder $query, string $column, array $values, string $logic): Builder
    {
        if ($logic === 'or') {
            return $query->orWhereIn($column, $values);
        }

        // Default to 'and' logic
        return $query->whereIn($column, $values);
    }
}
