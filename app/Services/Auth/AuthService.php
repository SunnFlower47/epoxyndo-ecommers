<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class AuthService
{
    /**
     * Handle user login.
     */
    public function login(array $credentials): bool
    {
        return Auth::attempt($credentials);
    }

    /**
     * Handle user registration.
     */
    public function register(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    /**
     * Handle user logout.
     */
    public function logout(): void
    {
        Auth::logout();
    }

    /**
     * Handle Google OAuth login or registration.
     */
    public function handleGoogleCallback(SocialiteUser $socialiteUser): User
    {
        $user = User::where('google_id', $socialiteUser->getId())
            ->orWhere('email', $socialiteUser->getEmail())
            ->first();

        if ($user) {
            if (!$user->google_id) {
                $user->update(['google_id' => $socialiteUser->getId()]);
            }
        } else {
            $user = User::create([
                'name' => $socialiteUser->getName(),
                'email' => $socialiteUser->getEmail(),
                'google_id' => $socialiteUser->getId(),
                'password' => Hash::make(uniqid()), // Random password
            ]);
        }

        Auth::login($user);

        return $user;
    }
}
