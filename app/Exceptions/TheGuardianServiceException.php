<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class TheGuardianServiceException extends Exception
{
    /**
     * Report the exception.
     *
     * @codeCoverageIgnore
     */
    public function report(): void
    {
        //
    }

    /**
     * Render the exception as an HTTP response.
     *
     * @codeCoverageIgnore
     */
    public function render(Request $request): JsonResponse
    {

        return response()->json([
            'service' => 'The Guardian API Service',
            'message' => $this->getMessage(),
        ], 500);
    }
}
