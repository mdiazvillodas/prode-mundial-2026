<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class BrevoTransactionalEmailService
{
    private const ENDPOINT = 'https://api.brevo.com/v3/smtp/email';

    public function sendVerificationCode(User $user, string $code): void
    {
        $apiKey = (string) config('services.brevo.api_key');

        if ($apiKey === '') {
            Log::error('Brevo API key is missing for verification email delivery.');

            throw new RuntimeException('Brevo API key is missing.');
        }

        $fromEmail = (string) config('services.brevo.transactional_from_email');
        $fromName = (string) config('services.brevo.transactional_from_name');

        if ($fromEmail === '') {
            Log::error('Brevo transactional sender email is missing.');

            throw new RuntimeException('Brevo transactional sender email is missing.');
        }

        try {
            $response = Http::withHeaders([
                'api-key' => $apiKey,
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ])
                ->timeout((int) config('services.brevo.api_timeout', 10))
                ->post(self::ENDPOINT, [
                    'sender' => [
                        'name' => $fromName,
                        'email' => $fromEmail,
                    ],
                    'to' => [
                        [
                            'email' => $user->email,
                            'name' => $user->name,
                        ],
                    ],
                    'subject' => 'Tu código de verificación de Mi Prode',
                    'htmlContent' => $this->htmlContent($user, $code),
                    'textContent' => $this->textContent($user, $code),
                ]);
        } catch (ConnectionException $exception) {
            Log::error('Brevo verification email request failed.', [
                'user_id' => $user->id,
                'exception' => $exception,
            ]);

            throw new RuntimeException('Brevo verification email request failed.', previous: $exception);
        }

        if (! $response->successful()) {
            Log::error('Brevo verification email returned an error response.', [
                'user_id' => $user->id,
                'status' => $response->status(),
                'body' => str($response->body())->limit(1000)->toString(),
            ]);

            throw new RuntimeException('Brevo verification email returned an error response.');
        }
    }

    private function htmlContent(User $user, string $code): string
    {
        $safeName = e($user->name);
        $safeCode = e($code);
        $expiresIn = EmailVerificationCodeService::EXPIRES_IN_MINUTES;

        return <<<HTML
            <p>Hola {$safeName},</p>
            <p>Tu código de verificación de Mi Prode es:</p>
            <p style="font-size: 28px; font-weight: 700; letter-spacing: 6px;">{$safeCode}</p>
            <p>Ingresalo en Mi Prode para activar tu cuenta. Este código vence en {$expiresIn} minutos.</p>
            <p>Si no creaste una cuenta, podés ignorar este mensaje.</p>
        HTML;
    }

    private function textContent(User $user, string $code): string
    {
        return sprintf(
            "Hola %s,\n\nTu código de verificación de Mi Prode es: %s\n\nIngresalo en Mi Prode para activar tu cuenta. Este código vence en %d minutos.\n\nSi no creaste una cuenta, podés ignorar este mensaje.",
            $user->name,
            $code,
            EmailVerificationCodeService::EXPIRES_IN_MINUTES,
        );
    }
}
