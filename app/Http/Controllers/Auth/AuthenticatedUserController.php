<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\AuthenticateUserAction;
use App\Http\Requests\Auth\AuthenticatedUserRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

final readonly class AuthenticatedUserController
{
    /**
     * Handle an incoming login request.
     */
    public function store(AuthenticatedUserRequest $request): JsonResponse
    {
        try {

            $data = $request->validated();

            [$user, $token] = (new AuthenticateUserAction())->handle($data);

            return response()->json(['message' => 'User logged in successfully.', 'user' => $user, 'token' => $token], 200);

        } catch (InvalidArgumentException $e) {

            Log::error($e->getMessage());

            return response()->json(['message' => $e->getMessage()], 400);

        } catch (Exception $e) {// @codeCoverageIgnoreStart

            Log::error('Error occurred while logging in: '.$e->getMessage());

            return response()->json(['message' => 'Error occurred while logging in.'], 500);

        }// @codeCoverageIgnoreEnd
    }
}
