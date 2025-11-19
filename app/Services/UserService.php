<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

final class UserService
{
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function login(string $email, string $password) : string
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw new Exception("User not found", Response::HTTP_NOT_FOUND);
        }

        if (!Hash::check($password, $user->password)) {
            throw new \Exception('Invalid credentials', Response::HTTP_FORBIDDEN);
        }

        return $this->userRepository->createToken($user);
    }

    public function logout() : void
    {
        $user = Auth::user();

        $this->userRepository->revokeToken($user);
    }
}
