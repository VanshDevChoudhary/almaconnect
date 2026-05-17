<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirect;

class GoogleController extends Controller
{
    public function redirect(): SymfonyRedirect
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            Log::warning('Google OAuth callback failed', ['error' => $e->getMessage()]);

            return redirect()->route('login')->withErrors([
                'email' => 'Google sign-in failed. Please try again or use email and password.',
            ]);
        }

        $user = User::where('google_id', $googleUser->getId())->first();

        if (! $user && $googleUser->getEmail()) {
            $user = User::where('email', $googleUser->getEmail())->first();

            // Account exists from password registration — link Google to it.
            if ($user && ! $user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }
        }

        if (! $user) {
            $user = DB::transaction(function () use ($googleUser) {
                $user = User::create([
                    'name' => $googleUser->getName() ?: 'AlmaConnect Member',
                    'email' => $googleUser->getEmail(),
                    'password' => null,
                    'google_id' => $googleUser->getId(),
                    'role' => 'alumni',
                    'status' => 'pending',
                ]);

                // Google has already verified the email address.
                $user->markEmailAsVerified();

                Profile::create(['user_id' => $user->id]);

                return $user;
            });
        }

        Auth::login($user, remember: true);

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
