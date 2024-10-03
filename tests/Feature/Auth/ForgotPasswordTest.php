<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Password;

it('sends a password reset link successfully', function (): void {

    $user = User::factory()->create([
        'email' => 'john.doe@example.com',
    ]);

    Password::shouldReceive('sendResetLink')
        ->once()
        ->with(['email' => $user->email])
        ->andReturn(Password::RESET_LINK_SENT);

    $response = $this->postJson(route('password.forgot'), [
        'email' => $user->email,
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => __(Password::RESET_LINK_SENT)]);
});

it('fails to send password reset link for non-existent email', function (): void {

    $email = 'nonexistent@example.com';

    Password::shouldReceive('sendResetLink')
        ->once()
        ->with(['email' => $email])
        ->andReturn(Password::INVALID_USER);

    $response = $this->postJson(route('password.forgot'), [
        'email' => $email,
    ]);

    $response->assertStatus(400)
        ->assertJson(['message' => __(Password::INVALID_USER)]);
});
