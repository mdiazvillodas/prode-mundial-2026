<?php

namespace Tests\Feature\Auth;

use App\Models\EmailVerificationCode;
use App\Models\User;
use Database\Seeders\StagingDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var array<int, array<string, mixed>>
     */
    private array $brevoPayloads = [];

    public function test_registration_creates_and_sends_verification_code_through_brevo_api(): void
    {
        $this->configureBrevo();
        $this->fakeSuccessfulBrevo();

        $this->post('/register', [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('verification.code.show', absolute: false));

        $user = User::query()->where('email', 'test@example.com')->firstOrFail();
        $verificationCode = EmailVerificationCode::query()->whereBelongsTo($user)->firstOrFail();
        $plainCode = $this->latestBrevoCode();

        $this->assertMatchesRegularExpression('/^\d{6}$/', $plainCode);
        $this->assertNotSame($plainCode, $verificationCode->code_hash);
        $this->assertTrue(Hash::check($plainCode, $verificationCode->code_hash));
        $this->assertSame('test@example.com', $this->latestBrevoPayload()['to'][0]['email']);

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
        $this->configureBrevo();
        $this->fakeSuccessfulBrevo();

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
        $this->configureBrevo();
        $this->fakeSuccessfulBrevo();

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
        $this->configureBrevo();
        $this->fakeSuccessfulBrevo();

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

    public function test_resend_code_sends_new_brevo_email_and_supersedes_previous_code(): void
    {
        $this->configureBrevo();
        $this->fakeSuccessfulBrevo();

        $user = User::factory()->unverified()->create();
        $oldCode = $this->sendAndCaptureCode($user);

        $this->actingAs($user)
            ->post(route('verification.code.resend'))
            ->assertRedirect();

        Http::assertSentCount(2);

        $this->actingAs($user)
            ->post(route('verification.code.verify'), ['code' => $oldCode])
            ->assertSessionHasErrors('code');

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
        $this->assertSame(2, EmailVerificationCode::query()->whereBelongsTo($user)->count());
        $this->assertSame(1, EmailVerificationCode::query()->whereBelongsTo($user)->whereNull('used_at')->count());
    }

    public function test_resend_code_missing_brevo_api_key_redirects_without_500(): void
    {
        $user = User::factory()->unverified()->create();
        config(['services.brevo.api_key' => null]);
        Http::fake();

        $this->actingAs($user)
            ->post(route('verification.code.resend'))
            ->assertRedirect()
            ->assertSessionHas('error', 'No pudimos reenviar el código de verificación. Probá de nuevo en unos minutos.');

        Http::assertNothingSent();
    }

    public function test_resend_code_brevo_non_2xx_response_redirects_without_500(): void
    {
        $this->configureBrevo();
        $user = User::factory()->unverified()->create();

        Http::fake([
            'https://api.brevo.com/v3/smtp/email' => Http::response(['message' => 'Unauthorized'], 401),
        ]);

        $this->actingAs($user)
            ->post(route('verification.code.resend'))
            ->assertRedirect()
            ->assertSessionHas('error', 'No pudimos reenviar el código de verificación. Probá de nuevo en unos minutos.');

        Http::assertSentCount(1);
    }

    public function test_resend_code_brevo_connection_exception_redirects_without_500(): void
    {
        $this->configureBrevo();
        $user = User::factory()->unverified()->create();

        Http::fake(function (): never {
            throw new ConnectionException('Connection timed out');
        });

        $this->actingAs($user)
            ->post(route('verification.code.resend'))
            ->assertRedirect()
            ->assertSessionHas('error', 'No pudimos reenviar el código de verificación. Probá de nuevo en unos minutos.');
    }

    public function test_verification_notification_brevo_failure_redirects_without_500(): void
    {
        $this->configureBrevo();
        $user = User::factory()->unverified()->create();

        Http::fake([
            'https://api.brevo.com/v3/smtp/email' => Http::response(['message' => 'Unauthorized'], 401),
        ]);

        $this->actingAs($user)
            ->post(route('verification.send'))
            ->assertRedirect()
            ->assertSessionHas('error', 'No pudimos reenviar el código de verificación. Probá de nuevo en unos minutos.');
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

        return $this->latestBrevoCode();
    }

    private function configureBrevo(): void
    {
        config([
            'services.brevo.api_key' => 'fake-brevo-key',
            'services.brevo.transactional_from_email' => 'no-reply@miprode.es',
            'services.brevo.transactional_from_name' => 'Mi Prode',
            'services.brevo.api_timeout' => 10,
        ]);
    }

    private function fakeSuccessfulBrevo(): void
    {
        $this->brevoPayloads = [];

        Http::fake([
            'https://api.brevo.com/v3/smtp/email' => function (Request $request) {
                $this->brevoPayloads[] = $request->data();

                return Http::response(['messageId' => 'brevo-message-id'], 201);
            },
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function latestBrevoPayload(): array
    {
        $this->assertNotEmpty($this->brevoPayloads);

        $payload = end($this->brevoPayloads);

        $this->assertIsArray($payload);

        return $payload;
    }

    private function latestBrevoCode(): string
    {
        $payload = $this->latestBrevoPayload();

        preg_match('/\b(\d{6})\b/', (string) $payload['textContent'], $matches);

        $this->assertArrayHasKey(1, $matches);

        return $matches[1];
    }
}
