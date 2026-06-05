<?php

namespace Tests\Feature\Auth;

use App\Mail\EmailVerificationCodeMail;
use App\Models\EmailVerificationCode;
use App\Models\User;
use App\Services\EmailVerificationCodeService;
use Database\Seeders\StagingDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_and_sends_verification_code_mail(): void
    {
        Mail::fake();

        $this->post('/register', [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('verification.code.show', absolute: false));

        $user = User::query()->where('email', 'test@example.com')->firstOrFail();
        $verificationCode = EmailVerificationCode::query()->whereBelongsTo($user)->firstOrFail();

        Mail::assertSent(EmailVerificationCodeMail::class, function (EmailVerificationCodeMail $mail) use ($user, $verificationCode): bool {
            $this->assertSame($user->email, $mail->hasTo($user->email) ? $user->email : null);
            $this->assertMatchesRegularExpression('/^\d{6}$/', $mail->code);
            $this->assertNotSame($mail->code, $verificationCode->code_hash);
            $this->assertTrue(Hash::check($mail->code, $verificationCode->code_hash));

            return $mail->user->is($user);
        });

        $this->assertAuthenticatedAs($user);
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_unverified_user_is_redirected_to_verification_screen_from_dashboard(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($user)
            ->get(route('verification.notice'))
            ->assertRedirect(route('verification.code.show'));
    }

    public function test_verified_user_can_access_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_valid_code_verifies_email(): void
    {
        Mail::fake();

        $user = User::factory()->unverified()->create();
        $plainCode = $this->sendAndCaptureCode($user);

        $this->actingAs($user)
            ->post(route('verification.code.verify'), ['code' => $plainCode])
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertTrue(EmailVerificationCode::query()->whereBelongsTo($user)->firstOrFail()->isUsed());
    }

    public function test_invalid_code_fails(): void
    {
        Mail::fake();

        $user = User::factory()->unverified()->create();
        $this->sendAndCaptureCode($user);

        $this->actingAs($user)
            ->post(route('verification.code.verify'), ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_expired_code_fails(): void
    {
        $user = User::factory()->unverified()->create();

        EmailVerificationCode::factory()
            ->for($user)
            ->expired()
            ->create([
                'code_hash' => Hash::make('123456'),
            ]);

        $this->actingAs($user)
            ->post(route('verification.code.verify'), ['code' => '123456'])
            ->assertSessionHasErrors('code');

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_used_code_cannot_be_reused(): void
    {
        Mail::fake();

        $user = User::factory()->unverified()->create();
        $plainCode = $this->sendAndCaptureCode($user);

        $this->actingAs($user)
            ->post(route('verification.code.verify'), ['code' => $plainCode])
            ->assertRedirect(route('dashboard', absolute: false));

        $user->forceFill(['email_verified_at' => null])->save();

        $this->actingAs($user)
            ->post(route('verification.code.verify'), ['code' => $plainCode])
            ->assertSessionHasErrors('code');

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_resend_code_sends_new_mail_and_supersedes_previous_code(): void
    {
        Mail::fake();

        $user = User::factory()->unverified()->create();
        $oldCode = $this->sendAndCaptureCode($user);

        $this->actingAs($user)
            ->post(route('verification.code.resend'))
            ->assertRedirect();

        Mail::assertSent(EmailVerificationCodeMail::class, 2);

        $this->actingAs($user)
            ->post(route('verification.code.verify'), ['code' => $oldCode])
            ->assertSessionHasErrors('code');

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
        $this->assertSame(2, EmailVerificationCode::query()->whereBelongsTo($user)->count());
        $this->assertSame(1, EmailVerificationCode::query()->whereBelongsTo($user)->whereNull('used_at')->count());
    }

    public function test_resend_code_mail_failure_redirects_without_500(): void
    {
        Log::spy();

        $user = User::factory()->unverified()->create();

        $this->mock(EmailVerificationCodeService::class, function ($mock): void {
            $mock->shouldReceive('sendCode')
                ->once()
                ->andThrow(new RuntimeException('SMTP failed'));
        });

        $this->actingAs($user)
            ->post(route('verification.code.resend'))
            ->assertRedirect()
            ->assertSessionHas('error', 'No pudimos reenviar el código de verificación. Probá de nuevo en unos minutos.');

        Log::shouldHaveReceived('error')
            ->once()
            ->withArgs(fn (string $message, array $context): bool => $message === 'Failed to resend verification code.'
                && ($context['user_id'] ?? null) === $user->id
                && ($context['exception'] ?? null) instanceof RuntimeException);
    }

    public function test_verification_notification_mail_failure_redirects_without_500(): void
    {
        Log::spy();

        $user = User::factory()->unverified()->create();

        $this->mock(EmailVerificationCodeService::class, function ($mock): void {
            $mock->shouldReceive('sendCode')
                ->once()
                ->andThrow(new RuntimeException('SMTP failed'));
        });

        $this->actingAs($user)
            ->post(route('verification.send'))
            ->assertRedirect()
            ->assertSessionHas('error', 'No pudimos reenviar el código de verificación. Probá de nuevo en unos minutos.');

        Log::shouldHaveReceived('error')
            ->once()
            ->withArgs(fn (string $message, array $context): bool => $message === 'Failed to resend verification notification code.'
                && ($context['user_id'] ?? null) === $user->id
                && ($context['exception'] ?? null) instanceof RuntimeException);
    }

    public function test_demo_seeded_users_are_verified(): void
    {
        $this->seed(StagingDemoSeeder::class);

        foreach (['admin@prode.test', 'mariano@prode.test', 'ana@prode.test', 'juan@prode.test'] as $email) {
            $this->assertTrue(User::query()->where('email', $email)->firstOrFail()->hasVerifiedEmail());
        }
    }

    public function test_traditional_login_still_works_for_verified_users(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    private function sendAndCaptureCode(User $user): string
    {
        app(\App\Services\EmailVerificationCodeService::class)->sendCode($user);

        $plainCode = null;

        Mail::assertSent(EmailVerificationCodeMail::class, function (EmailVerificationCodeMail $mail) use ($user, &$plainCode): bool {
            if (! $mail->user->is($user)) {
                return false;
            }

            $plainCode = $mail->code;

            return true;
        });

        $this->assertIsString($plainCode);

        return $plainCode;
    }
}
