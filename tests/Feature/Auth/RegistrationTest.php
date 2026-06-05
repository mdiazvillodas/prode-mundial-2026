<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var array<int, array<string, mixed>>
     */
    private array $brevoPayloads = [];

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $this->configureBrevo();
        $this->fakeSuccessfulBrevo();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('verification.code.show', absolute: false));
        $this->assertBrevoSentTo('test@example.com', 'Test User');
    }

    public function test_register_page_includes_csrf_token(): void
    {
        $response = $this->get('/register');

        $response->assertOk();
        $response->assertSee('name="_token"', false);
    }

    public function test_new_users_can_register_with_fresh_session_csrf_token(): void
    {
        $this->configureBrevo();
        $this->fakeSuccessfulBrevo();
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
        $this->assertBrevoSentTo('fresh@example.com', 'Fresh Session');
    }

    public function test_registration_missing_brevo_api_key_redirects_to_verification_without_500(): void
    {
        config(['services.brevo.api_key' => null]);
        Http::fake();

        $response = $this->post('/register', [
            'name' => 'Missing Brevo',
            'username' => 'missingbrevo',
            'email' => 'missing-brevo@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::query()->where('email', 'missing-brevo@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('verification.code.show', absolute: false));
        $response->assertSessionHas('error', 'Creamos tu cuenta, pero no pudimos enviar el código de verificación. Probá reenviarlo en unos minutos.');
        Http::assertNothingSent();
    }

    public function test_registration_brevo_non_2xx_response_redirects_to_verification_without_500(): void
    {
        $this->configureBrevo();

        Http::fake([
            'https://api.brevo.com/v3/smtp/email' => Http::response(['message' => 'Unauthorized'], 401),
        ]);

        $response = $this->post('/register', [
            'name' => 'Brevo Error',
            'username' => 'brevoerror',
            'email' => 'brevo-error@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::query()->where('email', 'brevo-error@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('verification.code.show', absolute: false));
        $response->assertSessionHas('error', 'Creamos tu cuenta, pero no pudimos enviar el código de verificación. Probá reenviarlo en unos minutos.');
        Http::assertSentCount(1);
    }

    public function test_registration_brevo_connection_exception_redirects_to_verification_without_500(): void
    {
        $this->configureBrevo();

        Http::fake(function (): never {
            throw new ConnectionException('Connection timed out');
        });

        $response = $this->post('/register', [
            'name' => 'Brevo Timeout',
            'username' => 'brevotimeout',
            'email' => 'brevo-timeout@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::query()->where('email', 'brevo-timeout@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('verification.code.show', absolute: false));
        $response->assertSessionHas('error', 'Creamos tu cuenta, pero no pudimos enviar el código de verificación. Probá reenviarlo en unos minutos.');
    }

    public function test_users_can_not_register_with_duplicate_username(): void
    {
        $this->configureBrevo();
        $this->fakeSuccessfulBrevo();

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

    private function assertBrevoSentTo(string $email, string $name): void
    {
        Http::assertSentCount(count($this->brevoPayloads));

        $this->assertNotEmpty($this->brevoPayloads);
        $payload = end($this->brevoPayloads);

        $this->assertSame('no-reply@miprode.es', $payload['sender']['email']);
        $this->assertSame('Mi Prode', $payload['sender']['name']);
        $this->assertSame($email, $payload['to'][0]['email']);
        $this->assertSame($name, $payload['to'][0]['name']);
        $this->assertSame('Tu código de verificación de Mi Prode', $payload['subject']);
        $this->assertMatchesRegularExpression('/\b\d{6}\b/', $payload['textContent']);
    }
}
