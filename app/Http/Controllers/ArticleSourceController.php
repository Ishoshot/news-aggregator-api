<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Internal\ArticleSourceService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

final class ArticleSourceController
{
    /**
     * List all article sources.
     */
    public function index(): JsonResponse
    {
        try {

            $sources = (new ArticleSourceService())->get();

            return response()->json(['message' => 'Article sources retrieved successfully.', 'data' => $sources], 200);

        } catch (Exception $e) {// @codeCoverageIgnoreStart

            Log::error('Error occurred while retrieving article sources: '.$e->getMessage());

            return response()->json(['message' => 'Error occurred while retrieving article sources.'], 500);

        }// @codeCoverageIgnoreEnd
    }
}
