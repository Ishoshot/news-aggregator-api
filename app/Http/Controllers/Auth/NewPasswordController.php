<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\ResetPasswordAction;
use App\Http\Requests\Auth\NewPasswordRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

final readonly class NewPasswordController
{
    /**
     * Handle an incoming password update request.
     */
    public function store(NewPasswordRequest $request): JsonResponse
    {
        try {

            return (new ResetPasswordAction())->handle($request);

        } catch (Exception $e) {// @codeCoverageIgnoreStart

            Log::error('Error occurred while updating password: '.$e->getMessage());

            return response()->json(['message' => 'Error occurred while updating password.'], 500);

        }// @codeCoverageIgnoreEnd
    }
}
