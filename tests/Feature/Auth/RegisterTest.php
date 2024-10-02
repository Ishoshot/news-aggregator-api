<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('new users can register successfully', function (): void {

    $data = [
        'name' => 'John Doe',
        'email' => 'john.doe@innoscripta.com',
        'password' => 'Secret123$',
        'password_confirmation' => 'Secret123$',
    ];

    $response = $this->postJson(route('auth.register'), $data);

    $response->assertStatus(201)
        ->assertJson(['message' => 'User created successfully.']);

    $this->assertDatabaseHas('users', [
        'email' => 'john.doe@innoscripta.com',
    ]);

});

test('user password is hashed correctly', function (): void {

    $data = [
        'name' => 'John Doe',
        'email' => 'john.doe@innoscripta.com',
        'password' => 'Secret123$',
        'password_confirmation' => 'Secret123$',
    ];

    $response = $this->postJson(route('auth.register'), $data);

    $response->assertStatus(201)
        ->assertJson(['message' => 'User created successfully.']);

    $this->assertDatabaseHas('users', [
        'email' => 'john.doe@innoscripta.com',
    ]);

    $user = User::where('email', 'john.doe@innoscripta.com')->first();

    expect(Hash::check('Secret123$', $user->password))->toBeTrue();
});

test('password confirmation must match', function (): void {

    $data = [
        'name' => 'John Doe',
        'email' => 'john.doe@innoscripta.com',
        'password' => 'Secret123$',
        'password_confirmation' => 'Secret123',
    ];

    $response = $this->postJson(route('auth.register'), $data);

    $response->assertStatus(422)
        ->assertJson(['message' => 'The password field confirmation does not match.']);

});

test('important fields are required', function (string $field): void {

    $data = [
        'name' => 'John Doe',
        'email' => 'john.doe@innoscripta.com',
        'password' => 'Secret123$',
        'password_confirmation' => 'Secret123$',
    ];

    $data[$field] = '';

    $response = $this->postJson(route('auth.register'), $data);

    $response->assertStatus(422)
        ->assertJson(['message' => 'The '.$field.' field is required.']);

})->with(['name', 'email', 'password']);

test('email field must be valid', function (): void {
    $data = [
        'name' => 'John Doe',
        'email' => 'invalid-email',
        'password' => 'Secret123$',
        'password_confirmation' => 'Secret123$',
    ];

    $response = $this->postJson(route('auth.register'), $data);

    $response->assertStatus(422)
        ->assertJson(['message' => 'The email field must be a valid email address.']);
});

test('email provider must be authorized', function (): void {
    $data = [
        'name' => 'John Doe',
        'email' => 'tobby@mailinator.com',
        'password' => 'Secret123$',
        'password_confirmation' => 'Secret123$',
    ];

    $response = $this->postJson(route('auth.register'), $data);

    $response->assertStatus(422)
        ->assertJson(['message' => 'The email belongs to an unauthorized email provider.']);
});

test('fails due to rate limiting', function (): void {

    $data = [
        'name' => 'John Doe',
        'email' => 'testing123@example.com',
        'password' => 'Secret123$',
        'password_confirmation' => 'Secret123$',
    ];

    // Send 12 successful requests
    for ($i = 0; $i < 12; $i++) {
        $data['email'] = "testing123{$i}@example.com";
        $this->postJson(route('auth.register'), $data)->assertStatus(201);
    }

    // Next should should fail
    $response = $this->postJson(route('auth.register'), $data);

    $response->assertStatus(429);
});

test('email must be unique', function (): void {

    $data = [
        'name' => 'John Doe',
        'email' => 'unique@example.com',
        'password' => 'Secret123$',
        'password_confirmation' => 'Secret123$',
    ];

    $this->postJson(route('auth.register'), $data)->assertStatus(201);

    $this->postJson(route('auth.register'), $data)->assertStatus(422)->assertJson(['message' => 'The email has already been taken.']);
});
