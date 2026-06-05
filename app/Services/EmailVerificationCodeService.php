<?php

namespace App\Services;

use App\Mail\EmailVerificationCodeMail;
use App\Models\EmailVerificationCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class EmailVerificationCodeService
{
    public const EXPIRES_IN_MINUTES = 15;

    public function sendCode(User $user): EmailVerificationCode
    {
        $plainCode = $this->generatePlainCode();

        $verificationCode = DB::transaction(function () use ($user, $plainCode): EmailVerificationCode {
            EmailVerificationCode::query()
                ->where('user_id', $user->id)
                ->whereNull('used_at')
                ->update(['used_at' => now()]);

            return EmailVerificationCode::query()->create([
                'user_id' => $user->id,
                'code_hash' => Hash::make($plainCode),
                'expires_at' => now()->addMinutes(self::EXPIRES_IN_MINUTES),
            ]);
        });

        Mail::to($user->email)->send(new EmailVerificationCodeMail($user, $plainCode));

        return $verificationCode;
    }

    public function verify(User $user, string $plainCode): bool
    {
        $verificationCode = EmailVerificationCode::query()
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->latest()
            ->first();

        if (! $verificationCode || $verificationCode->isExpired()) {
            return false;
        }

        if (! Hash::check($plainCode, $verificationCode->code_hash)) {
            return false;
        }

        DB::transaction(function () use ($user, $verificationCode): void {
            $verificationCode->forceFill([
                'used_at' => now(),
            ])->save();

            if (! $user->hasVerifiedEmail()) {
                $user->forceFill([
                    'email_verified_at' => now(),
                ])->save();
            }
        });

        return true;
    }

    private function generatePlainCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
