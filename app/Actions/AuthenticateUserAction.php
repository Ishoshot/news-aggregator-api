<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

final readonly class AuthenticateUserAction
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle user authentication.
     *
     * @param  array<string, mixed>  $credentials
     * @return array<int, \App\Models\User|string>
     *
     * @throws InvalidArgumentException
     */
    public function handle(array $credentials): array
    {

        $email = type($credentials['email'])->asString();

        $password = type($credentials['password'])->asString();

        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw new InvalidArgumentException('The provided credentials are incorrect.', 401);
        }

        // Force delete old tokens
        $user->tokens()->delete();

        // Create token
        $token = $user->createToken($email)->plainTextToken;

        return [$user, $token];
    }
}
