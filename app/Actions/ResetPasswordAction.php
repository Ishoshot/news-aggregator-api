<?php

declare(strict_types=1);

namespace App\Actions;

use App\Http\Requests\Auth\NewPasswordRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

final readonly class ResetPasswordAction
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle send password reset link.
     */
    public function handle(NewPasswordRequest $request): JsonResponse
    {

        $status = Password::reset(
            $request->only('email', 'token', 'password', 'password_confirmation'),
            function (User $user) use ($request): void {
                $user->forceFill([
                    'password' => Hash::make($request->string('password')->value()),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        $status = type($status)->asString();

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => __($status)], 200)
            : response()->json(['message' => __($status)], 400);
    }
}
