<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Requests\Auth\RegisteredUserRequest;
use App\Services\Internal\UserService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

final readonly class RegisteredUserController
{
    /**
     * Handle an incoming registration request.
     */
    public function store(RegisteredUserRequest $request): JsonResponse
    {
        try {

            $data = $request->validated();

            $user = (new UserService())->create($data);

            return response()->json(['message' => 'User created successfully.', 'user' => $user], 201);

        } catch (Exception $e) {// @codeCoverageIgnoreStart

            Log::error('Error occurred while creating user: '.$e->getMessage());

            return response()->json(['message' => 'Error occurred while creating user.'], 500);

        }// @codeCoverageIgnoreEnd
    }
}
