<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    // create a new user
    public function register(array $userData): array
    {
        $user = User::create([
            'name' => $userData['name'],
            'cpf' => $userData['cpf'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
        ]);

        $token = $user->createToken('auth_token')->accessToken;

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ];
    }

    // authenticate user with email and password
    public function login(string $email, string $password): ?array
    {
        if (!Auth::attempt(['email' => $email, 'password' => $password])) {
            return null;
        }

        $user = User::where('email', $email)->first();
        $token = $user->createToken('auth_token')->accessToken;

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ];
    }

    // logout user
    public function logout($request): bool
    {
        $request->user()->token()->revoke();
        return true;
    }
}
