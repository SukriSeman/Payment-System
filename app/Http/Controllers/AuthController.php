<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuthController extends Controller
{
    public function __construct(private UserService $userService) { }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $token = $this->userService->login($request->get('email'), $request->get('password'));

        if (empty($token)) {

            return Response()->json([
                'status' => 'error',
                'message' => 'Something went wrong, please try again.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);

        } else {

            return Response()->json([
                'status' => 'success',
                'data' => [
                    'token' => $token
                ]
            ]);

        }
    }

    public function logout()
    {
        $this->userService->logout();

        return Response()->json([
            'status' => 'success',
            'data' => []
        ]);

    }
}
