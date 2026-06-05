<?php

namespace Tests\Feature\Auth;

use App\Mail\EmailVerificationCodeMail;
use App\Models\User;
use App\Services\EmailVerificationCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        Mail::fake();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('verification.code.show', absolute: false));
        Mail::assertSent(EmailVerificationCodeMail::class);
    }

    public function test_register_page_includes_csrf_token(): void
    {
        $response = $this->get('/register');

        $response->assertOk();
        $response->assertSee('name="_token"', false);
    }

    public function test_new_users_can_register_with_fresh_session_csrf_token(): void
    {
        Mail::fake();
        $this->withMiddleware();

        $this->get('/register')->assertOk();
        $token = session()->token();

        $response = $this->post('/register', [
            '_token' => $token,
            'name' => 'Fresh Session',
            'username' => 'freshsession',
            'email' => 'fresh@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('verification.code.show', absolute: false));
        $this->assertDatabaseHas('users', ['email' => 'fresh@example.com']);
        Mail::assertSent(EmailVerificationCodeMail::class);
    }

    public function test_registration_mail_failure_redirects_to_verification_without_500(): void
    {
        Log::spy();

        $this->mock(EmailVerificationCodeService::class, function ($mock): void {
            $mock->shouldReceive('sendCode')
                ->once()
                ->andThrow(new RuntimeException('SMTP failed'));
        });

        $response = $this->post('/register', [
            'name' => 'Mail Failure',
            'username' => 'mailfailure',
            'email' => 'mail-failure@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::query()->where('email', 'mail-failure@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('verification.code.show', absolute: false));
        $response->assertSessionHas('error', 'Creamos tu cuenta, pero no pudimos enviar el código de verificación. Probá reenviarlo en unos minutos.');
        Log::shouldHaveReceived('error')
            ->once()
            ->withArgs(fn (string $message, array $context): bool => $message === 'Failed to send verification code after registration.'
                && ($context['user_id'] ?? null) === $user->id
                && ($context['exception'] ?? null) instanceof RuntimeException);
    }

    public function test_users_can_not_register_with_duplicate_username(): void
    {
        Mail::fake();

        $this->post('/register', [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        auth()->logout();

        $response = $this->post('/register', [
            'name' => 'Another User',
            'username' => 'testuser',
            'email' => 'another@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('username');
    }
}
