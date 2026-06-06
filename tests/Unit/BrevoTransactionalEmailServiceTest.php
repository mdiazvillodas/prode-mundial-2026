<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\BrevoTransactionalEmailService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Tests\TestCase;

class BrevoTransactionalEmailServiceTest extends TestCase
{
    public function test_successful_brevo_api_request_sends_verification_code(): void
    {
        $this->configureBrevo();

        Http::fake([
            'https://api.brevo.com/v3/smtp/email' => Http::response(['messageId' => 'brevo-message-id'], 201),
        ]);

        $user = new User([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $user->id = 123;

        app(BrevoTransactionalEmailService::class)->sendVerificationCode($user, '123456');

        Http::assertSentCount(1);
        Http::assertSent(function (Request $request): bool {
            $payload = $request->data();

            return $request->url() === 'https://api.brevo.com/v3/smtp/email'
                && $request->hasHeader('api-key', 'fake-brevo-key')
                && $request->hasHeader('accept', 'application/json')
                && $request->hasHeader('content-type', 'application/json')
                && $payload['sender']['email'] === 'no-reply@miprode.es'
                && $payload['sender']['name'] === 'Mi Prode'
                && $payload['to'][0]['email'] === 'test@example.com'
                && $payload['to'][0]['name'] === 'Test User'
                && $payload['subject'] === 'Tu código de verificación de Mi Prode'
                && str_contains($payload['htmlContent'], 'Mi Prode')
                && str_contains($payload['htmlContent'], 'Tu código de verificación')
                && str_contains($payload['htmlContent'], '123456')
                && str_contains($payload['htmlContent'], 'Este código vence en 15 minutos.')
                && str_contains($payload['htmlContent'], 'Si no creaste una cuenta en Mi Prode')
                && $payload['textContent'] === "Hola Test User\n\nTu código de verificación de Mi Prode es: 123456\n\nEste código vence en 15 minutos.\n\nSi no creaste una cuenta en Mi Prode, podés ignorar este mensaje.";
        });
    }

    public function test_missing_brevo_api_key_throws_clear_exception_without_request(): void
    {
        config(['services.brevo.api_key' => null]);
        Log::spy();
        Http::fake();

        try {
            app(BrevoTransactionalEmailService::class)->sendVerificationCode(new User([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]), '123456');

            $this->fail('Expected missing Brevo API key to throw.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Brevo API key is missing.', $exception->getMessage());
        }

        Http::assertNothingSent();
        Log::shouldHaveReceived('error')
            ->once()
            ->with('Brevo API key is missing for verification email delivery.');
    }

    public function test_non_successful_brevo_response_is_logged_and_throws(): void
    {
        $this->configureBrevo();
        Log::spy();

        Http::fake([
            'https://api.brevo.com/v3/smtp/email' => Http::response(['message' => 'Unauthorized'], 401),
        ]);

        try {
            app(BrevoTransactionalEmailService::class)->sendVerificationCode(new User([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]), '123456');

            $this->fail('Expected Brevo non-2xx response to throw.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Brevo verification email returned an error response.', $exception->getMessage());
        }

        Log::shouldHaveReceived('error')
            ->once()
            ->withArgs(fn (string $message, array $context): bool => $message === 'Brevo verification email returned an error response.'
                && ($context['status'] ?? null) === 401
                && str_contains((string) ($context['body'] ?? ''), 'Unauthorized'));
    }

    public function test_brevo_connection_exception_is_logged_and_throws(): void
    {
        $this->configureBrevo();
        Log::spy();

        Http::fake(function (): never {
            throw new ConnectionException('Connection timed out');
        });

        try {
            app(BrevoTransactionalEmailService::class)->sendVerificationCode(new User([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]), '123456');

            $this->fail('Expected Brevo connection exception to throw.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Brevo verification email request failed.', $exception->getMessage());
            $this->assertInstanceOf(ConnectionException::class, $exception->getPrevious());
        }

        Log::shouldHaveReceived('error')
            ->once()
            ->withArgs(fn (string $message, array $context): bool => $message === 'Brevo verification email request failed.'
                && ($context['exception'] ?? null) instanceof ConnectionException);
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
}
