<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        if (! $this->isGoogleConfigured()) {
            return $this->redirectWithGoogleError(__('El acceso con Google todavía no está configurado.'));
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        if (! $this->isGoogleConfigured()) {
            return $this->redirectWithGoogleError(__('El acceso con Google todavía no está configurado.'));
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable) {
            return $this->redirectWithGoogleError(__('No pudimos completar el acceso con Google. Intentá nuevamente.'));
        }

        if (! $googleUser->getEmail()) {
            return $this->redirectWithGoogleError(__('Google no devolvió un email para iniciar sesión.'));
        }

        $user = DB::transaction(function () use ($googleUser): User {
            $user = User::query()
                ->where('google_id', $googleUser->getId())
                ->first();

            if (! $user) {
                $user = User::query()
                    ->where('email', $googleUser->getEmail())
                    ->first();
            }

            if ($user) {
                $user->forceFill([
                    'google_id' => $googleUser->getId(),
                    'avatar_url' => $googleUser->getAvatar(),
                    'auth_provider' => 'google',
                ])->save();

                return $user;
            }

            return User::query()->create([
                'name' => $googleUser->getName() ?: Str::before($googleUser->getEmail(), '@'),
                'username' => $this->generateUsername($googleUser->getEmail()),
                'email' => $googleUser->getEmail(),
                'email_verified_at' => now(),
                'password' => Hash::make(Str::random(40)),
                'google_id' => $googleUser->getId(),
                'avatar_url' => $googleUser->getAvatar(),
                'auth_provider' => 'google',
            ]);
        });

        Auth::login($user);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function isGoogleConfigured(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'))
            && filled(config('services.google.redirect'));
    }

    private function redirectWithGoogleError(string $message): RedirectResponse
    {
        return redirect()
            ->route('login')
            ->withErrors(['google' => $message]);
    }

    private function generateUsername(string $email): string
    {
        $base = Str::of(Str::before($email, '@'))
            ->lower()
            ->replaceMatches('/[^a-z0-9_]+/', '_')
            ->trim('_')
            ->limit(30, '')
            ->value();

        if ($base === '') {
            $base = 'usuario';
        }

        $username = $base;
        $suffix = 2;

        while (User::query()->where('username', $username)->exists()) {
            $suffixText = (string) $suffix;
            $username = Str::limit($base, max(1, 30 - strlen($suffixText)), '').$suffixText;
            $suffix++;
        }

        return $username;
    }
}
