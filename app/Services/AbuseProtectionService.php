<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Throwable;

class AbuseProtectionService
{
    public const REGISTRATION_ERROR = 'Se alcanzó el límite temporal de registros. Probá más tarde.';

    public const RESEND_ERROR = 'Pediste varios códigos en poco tiempo. Esperá un momento antes de intentar de nuevo.';

    public function __construct(private BrevoTransactionalEmailService $brevoEmails) {}

    public function registrationCanProceed(Request $request): bool
    {
        $ip = (string) $request->ip();
        $ipKey = $this->registrationIpKey($ip);
        $ipLimit = $this->limit('registration_ip_hourly_limit');

        if ($this->limitReached($ipLimit) || RateLimiter::tooManyAttempts($ipKey, $ipLimit)) {
            Log::warning('Registration IP hourly limit reached.', [
                'ip' => $ip,
                'user_agent' => $request->userAgent(),
            ]);

            $this->sendAlertIfAllowed(
                'registration_ip_hourly_limit:'.$this->hashKeyPart($ip),
                'Se alcanzó el límite horario de registros por IP.',
                ['ip' => $ip, 'user_agent' => $request->userAgent()],
            );

            return false;
        }

        RateLimiter::hit($ipKey, 3600);

        $dailyLimit = $this->limit('registration_daily_limit');

        if ($this->limitReached($dailyLimit) || $this->count($this->registrationDailyKey()) >= $dailyLimit) {
            Log::warning('Registration daily limit reached.', [
                'ip' => $ip,
                'user_agent' => $request->userAgent(),
            ]);

            $this->sendAlertIfAllowed(
                'registration_daily_limit',
                'Se alcanzó el límite diario global de registros.',
                ['ip' => $ip, 'user_agent' => $request->userAgent()],
            );

            return false;
        }

        return true;
    }

    public function recordRegistrationCreated(): void
    {
        $count = $this->increment($this->registrationDailyKey(), now()->endOfDay());
        $limit = $this->limit('registration_daily_limit');

        if ($limit > 0 && $count >= $limit) {
            $this->sendAlertIfAllowed(
                'registration_daily_limit',
                'Se alcanzó el límite diario global de registros.',
            );
        }
    }

    public function logHoneypotTriggered(Request $request): void
    {
        Log::warning('Registration honeypot triggered.', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    public function verificationEmailCanBeSent(?User $user = null): bool
    {
        $dailyLimit = $this->limit('verification_email_daily_limit');

        if (! $this->limitReached($dailyLimit) && $this->count($this->verificationEmailDailyKey()) < $dailyLimit) {
            return true;
        }

        Log::warning('Verification email daily limit reached.', [
            'user_id' => $user?->id,
        ]);

        $this->sendAlertIfAllowed(
            'verification_email_daily_limit',
            'Se alcanzó el límite diario global de emails de verificación.',
            ['user_id' => $user?->id],
        );

        return false;
    }

    public function recordVerificationEmailSent(): void
    {
        $count = $this->increment($this->verificationEmailDailyKey(), now()->endOfDay());
        $limit = $this->limit('verification_email_daily_limit');

        if ($limit > 0 && $count >= $limit) {
            $this->sendAlertIfAllowed(
                'verification_email_daily_limit',
                'Se alcanzó el límite diario global de emails de verificación.',
            );
        }
    }

    public function resendCanProceed(User $user): bool
    {
        $cooldownKey = $this->resendCooldownKey($user);

        if (Cache::has($cooldownKey)) {
            Log::warning('Verification resend cooldown hit.', [
                'user_id' => $user->id,
            ]);

            return false;
        }

        $hourlyLimit = $this->limit('verification_resend_user_hourly_limit');

        if ($this->limitReached($hourlyLimit) || $this->count($this->resendHourlyKey($user)) >= $hourlyLimit) {
            Log::warning('Verification resend hourly limit reached.', [
                'user_id' => $user->id,
            ]);

            return false;
        }

        $dailyLimit = $this->limit('verification_resend_user_daily_limit');

        if ($this->limitReached($dailyLimit) || $this->count($this->resendDailyKey($user)) >= $dailyLimit) {
            Log::warning('Verification resend daily limit reached.', [
                'user_id' => $user->id,
            ]);

            return false;
        }

        return true;
    }

    public function recordVerificationResent(User $user): void
    {
        $cooldownSeconds = $this->limit('verification_resend_cooldown_seconds');

        if ($cooldownSeconds > 0) {
            Cache::put($this->resendCooldownKey($user), true, $cooldownSeconds);
        }

        $this->increment($this->resendHourlyKey($user), now()->addHour());
        $this->increment($this->resendDailyKey($user), now()->endOfDay());
    }

    private function sendAlertIfAllowed(string $type, string $message, array $context = []): void
    {
        $email = (string) config('abuse.alert_email', '');

        if ($email === '') {
            Log::info('Abuse alert skipped because no alert email is configured.', [
                'type' => $type,
            ]);

            return;
        }

        $cooldownMinutes = $this->limit('alert_cooldown_minutes');
        $cooldownKey = 'abuse_alert_sent:'.$type;

        if ($cooldownMinutes > 0 && ! Cache::add($cooldownKey, true, now()->addMinutes($cooldownMinutes))) {
            Log::info('Abuse alert skipped due to cooldown.', [
                'type' => $type,
            ]);

            return;
        }

        try {
            $this->brevoEmails->sendSecurityAlert($email, $message, $context);

            Log::info('Abuse alert sent.', [
                'type' => $type,
            ]);
        } catch (Throwable $exception) {
            Log::warning('Abuse alert failed.', [
                'type' => $type,
                'exception' => $exception,
            ]);
        }
    }

    private function increment(string $key, \DateTimeInterface $ttl): int
    {
        Cache::add($key, 0, $ttl);

        return (int) Cache::increment($key);
    }

    private function count(string $key): int
    {
        return (int) Cache::get($key, 0);
    }

    private function limit(string $key): int
    {
        return max(0, (int) config('abuse.'.$key));
    }

    private function limitReached(int $limit): bool
    {
        return $limit <= 0;
    }

    private function registrationIpKey(string $ip): string
    {
        return 'registration_ip_attempts:'.$this->hashKeyPart($ip);
    }

    private function registrationDailyKey(): string
    {
        return 'registrations_created:'.now()->toDateString();
    }

    private function verificationEmailDailyKey(): string
    {
        return 'verification_emails_sent:'.now()->toDateString();
    }

    private function resendCooldownKey(User $user): string
    {
        return 'verification_resend_cooldown:user:'.$user->id;
    }

    private function resendHourlyKey(User $user): string
    {
        return 'verification_resend_hourly:user:'.$user->id.':'.now()->format('Y-m-d-H');
    }

    private function resendDailyKey(User $user): string
    {
        return 'verification_resend_daily:user:'.$user->id.':'.now()->toDateString();
    }

    private function hashKeyPart(string $value): string
    {
        return sha1($value);
    }
}
