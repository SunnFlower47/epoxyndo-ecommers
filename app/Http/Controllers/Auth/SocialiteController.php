<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthService;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\RedirectResponse;

class SocialiteController extends Controller
{
    public function __construct(protected AuthService $authService)
    {
    }

    /**
     * Redirect to Google OAuth page.
     */
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback.
     */
    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $this->authService->handleGoogleCallback($googleUser);

            return redirect()->intended(route('dashboard'));
        } catch (\Exception $e) {
            return redirect(route('login'))->withErrors([
                'email' => 'Gagal login menggunakan akun Google. Silakan coba lagi.',
            ]);
        }
    }
}
