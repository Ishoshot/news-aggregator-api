<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\SendPasswordResetLinkAction;
use App\Http\Requests\Auth\PasswordResetLinkRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

final readonly class PasswordResetLinkController
{
    /**
     * Handle an incoming password reset link request.
     */
    public function store(PasswordResetLinkRequest $request): JsonResponse
    {
        try {

            return (new SendPasswordResetLinkAction())->handle($request);

        } catch (Exception $e) {// @codeCoverageIgnoreStart

            Log::error('Error occurred while sending password reset link: '.$e->getMessage());

            return response()->json(['message' => 'Error occurred while sending password reset link.'], 500);

        }// @codeCoverageIgnoreEnd
    }
}
