<?php

declare(strict_types=1);

namespace App\Services\Internal;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

final readonly class UserService
{
    /**
     * Create a new service instance
     */
    public function __construct()
    {
        //
    }

    /**
     * Create a new user
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make(type($data['password'])->asString()),
        ]);
    }
}
