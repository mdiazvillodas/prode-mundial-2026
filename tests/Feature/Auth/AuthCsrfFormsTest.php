<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthCsrfFormsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_auth_post_forms_render_csrf_tokens(): void
    {
        $user = User::factory()->create();
        $resetToken = Password::broker()->createToken($user);

        $this->assertPageHasCsrfToken('/login');
        $this->assertPageHasCsrfToken('/register');
        $this->assertPageHasCsrfToken('/forgot-password');
        $this->assertPageHasCsrfToken(route('password.reset', ['token' => $resetToken, 'email' => $user->email]));
    }

    public function test_email_verification_forms_render_csrf_tokens(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get(route('verification.code.show'));

        $response->assertOk();
        $response->assertSee('Código de verificación');
        $response->assertSee('Reenviar código');
        $response->assertSee('Cerrar sesión');
        $this->assertSame(3, substr_count($response->getContent(), 'name="_token"'));
    }

    public function test_legacy_verification_notice_forms_render_csrf_tokens(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get(route('verification.notice'));

        $response->assertRedirect(route('verification.code.show'));

        $view = $this->actingAs($user)->get('/email/verify-code');
        $this->assertSame(3, substr_count($view->getContent(), 'name="_token"'));
    }

    public function test_authenticated_navigation_logout_forms_render_csrf_tokens(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Cerrar sesion');
        $this->assertGreaterThanOrEqual(1, substr_count($response->getContent(), 'name="_token"'));
    }

    private function assertPageHasCsrfToken(string $uri): void
    {
        $response = $this->get($uri);

        $response->assertOk();
        $response->assertSee('name="_token"', false);
    }
}
