<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserPreferencesRequest;
use App\Models\ArticleAuthor;
use App\Models\ArticleCategory;
use App\Models\ArticleSource;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

final class UserArticlePreferenceController
{
    /**
     * Get user article preferences
     */
    public function index(): JsonResponse
    {
        try {

            $user = auth()->user();

            $user = type($user)->as(User::class);

            $user = $user->load(['articleSources', 'articleCategories', 'articleAuthors']);

            return response()->json([
                'message' => 'Article preferences retrieved successfully.',
                'data' => [
                    'article_sources' => $this->transformArticleData($user->articleSources),
                    'article_categories' => $this->transformArticleData($user->articleCategories),
                    'article_authors' => $this->transformArticleData($user->articleAuthors),
                ],
            ], 200);

        } catch (Exception $e) {// @codeCoverageIgnoreStart

            Log::error('Error occurred while retrieving user article preferences: '.$e->getMessage());

            return response()->json(['message' => 'Error occurred while retrieving user article preferences.'], 500);

        }// @codeCoverageIgnoreEnd
    }

    /**
     * Set user article preferences
     */
    public function store(UpdateUserPreferencesRequest $request): JsonResponse
    {
        try {

            $data = $request->validated();

            $user = auth()->user();

            $user = type($user)->as(User::class);

            $articleSources = type($data['article_sources'])->asArray();

            $articleCategories = type($data['article_categories'])->asArray();

            $articleAuthors = type($data['article_authors'])->asArray();

            $user->articleSources()->sync($articleSources);

            $user->articleCategories()->sync($articleCategories);

            $user->articleAuthors()->sync($articleAuthors);

            return response()->json(['message' => 'Article preferences updated successfully.'], 200);

        } catch (Exception $e) {// @codeCoverageIgnoreStart

            Log::error('Error occurred while updating user article preferences: '.$e->getMessage());

            return response()->json(['message' => 'Error occurred while updating user article preferences.'], 500);

        }// @codeCoverageIgnoreEnd
    }

    /**
     * Transform Article data
     *
     * @param  EloquentCollection<int, covariant ArticleSource|ArticleCategory|ArticleAuthor>  $data
     * @return Collection<int, array{id: string, name: string, slug: string, created_at: \Carbon\CarbonImmutable|null, updated_at:  \Carbon\CarbonImmutable|null}>
     */
    private function transformArticleData(EloquentCollection $data): Collection
    {
        return $data->map(fn (ArticleSource|ArticleCategory|ArticleAuthor $item): array => [
            'id' => $item->id,
            'name' => $item->name,
            'slug' => $item->slug,
            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
        ]);
    }
}
