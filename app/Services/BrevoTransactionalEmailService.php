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
            <div style="margin:0; padding:32px 16px; background:#f4f7fb; font-family:Arial, Helvetica, sans-serif; color:#0f172a;">
                <div style="max-width:520px; margin:0 auto; background:#ffffff; border:1px solid #e2e8f0; border-radius:18px; box-shadow:0 16px 40px rgba(15, 23, 42, 0.10); overflow:hidden;">
                    <div style="padding:28px 28px 24px;">
                        <p style="margin:0 0 18px; color:#2563eb; font-size:13px; font-weight:700; letter-spacing:0.08em; text-transform:uppercase;">Mi Prode</p>
                        <h1 style="margin:0 0 14px; color:#0f172a; font-size:24px; line-height:1.25; font-weight:800;">Tu código de verificación</h1>
                        <p style="margin:0 0 20px; color:#475569; font-size:15px; line-height:1.6;">Hola {$safeName}, usá este código para activar tu cuenta:</p>
                        <div style="margin:0 0 20px; padding:18px 16px; background:#eef6ff; border:1px solid #bfdbfe; border-radius:14px; text-align:center;">
                            <span style="display:inline-block; color:#0b3a75; font-size:32px; line-height:1; font-weight:800; letter-spacing:0.22em;">{$safeCode}</span>
                        </div>
                        <p style="margin:0; color:#475569; font-size:14px; line-height:1.6;">Este código vence en {$expiresIn} minutos.</p>
                    </div>
                    <div style="padding:18px 28px; background:#f8fafc; border-top:1px solid #e2e8f0;">
                        <p style="margin:0; color:#64748b; font-size:13px; line-height:1.5;">Si no creaste una cuenta en Mi Prode, podés ignorar este mensaje.</p>
                    </div>
                </div>
            </div>
        HTML;
    }

    private function textContent(User $user, string $code): string
    {
        return sprintf(
            "Hola %s\n\nTu código de verificación de Mi Prode es: %s\n\nEste código vence en %d minutos.\n\nSi no creaste una cuenta en Mi Prode, podés ignorar este mensaje.",
            $user->name,
            $code,
            EmailVerificationCodeService::EXPIRES_IN_MINUTES,
        );
    }
}
