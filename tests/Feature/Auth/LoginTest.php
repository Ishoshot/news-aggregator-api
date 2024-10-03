<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('users can log in successfully', function (): void {

    $user = User::factory()->create([
        'email' => 'john.doe@example.com',
        'password' => Hash::make('Secret123$'),
    ]);

    $data = [
        'email' => $user->email,
        'password' => 'Secret123$',
    ];

    $response = $this->postJson(route('auth.login'), $data);

    $response->assertStatus(200)
        ->assertJson(['message' => 'User logged in successfully.']);

});

it('fails to login with incorrect credentials', function (): void {

    $user = User::factory()->create([
        'email' => 'john01@example.com',
        'password' => Hash::make('Secret123$'),
    ]);

    $data = [
        'email' => $user->email,
        'password' => 'Secret123$c',
    ];

    $response = $this->postJson(route('auth.login'), $data);

    $response->assertStatus(400)
        ->assertJson(['message' => 'The provided credentials are incorrect.']);

});
