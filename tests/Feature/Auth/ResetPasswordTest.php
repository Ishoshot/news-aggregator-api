<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

it('resets the password successfully', function (): void {

    $user = User::factory()->create([
        'email' => 'john.doe@example.com',
        'password' => Hash::make('old-password'),
    ]);

    // Mock the Password facade to simulate a successful password reset
    Password::shouldReceive('reset')
        ->once()
        ->withArgs(function ($credentials, $callback) use ($user): bool {
            // Call the password reset callback
            $callback($user);

            return $credentials['email'] === 'john.doe@example.com' &&
                $credentials['password'] === 'New-password123$' &&
                $credentials['token'] === 'valid-token';
        })
        ->andReturn(Password::PASSWORD_RESET);

    $response = $this->postJson(route('password.reset'), [
        'email' => 'john.doe@example.com',
        'password' => 'New-password123$',
        'password_confirmation' => 'New-password123$',
        'token' => 'valid-token',
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => __(Password::PASSWORD_RESET)]);

    // Assert that the password was actually changed
    expect(Hash::check('New-password123$', $user->fresh()->password))->toBeTrue();
});

it('fails to reset password for non-existent email', function (): void {

    Password::shouldReceive('reset')
        ->once()
        ->withAnyArgs()
        ->andReturn(Password::INVALID_USER);

    $response = $this->postJson(route('password.reset'), [
        'email' => 'nonexistent@example.com',
        'password' => 'New-password123$',
        'password_confirmation' => 'New-password123$',
        'token' => 'valid-token',
    ]);

    $response->assertStatus(400)
        ->assertJson(['message' => __(Password::INVALID_USER)]);
});
