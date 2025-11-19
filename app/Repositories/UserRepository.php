<?php

namespace App\Repositories;

use App\Models\User;

final class UserRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new User());
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function createToken(User $user): string
    {
        $this->revokeToken($user); // delete old token if exist

        return $user->createToken('auth')->plainTextToken;
    }

    public function revokeToken(User $user): void
    {
        $user->tokens()->delete();
    }
}
