<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_redirect_route_redirects_when_config_is_present(): void
    {
        $this->configureGoogle();

        $provider = Mockery::mock();
        $provider->shouldReceive('redirect')
            ->once()
            ->andReturn(new RedirectResponse('https://accounts.google.com/oauth'));

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($provider);

        $this->get(route('auth.google.redirect'))
            ->assertRedirect('https://accounts.google.com/oauth');
    }

    public function test_missing_google_config_returns_gracefully_to_login(): void
    {
        config([
            'services.google.client_id' => null,
            'services.google.client_secret' => null,
            'services.google.redirect' => null,
        ]);

        $this->get(route('auth.google.redirect'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('google');
    }

    public function test_google_callback_creates_new_user(): void
    {
        $this->configureGoogle();
        $this->mockGoogleUser($this->googleUser(
            id: 'google-123',
            name: 'Mariano Demo',
            email: 'mariano@example.com',
            avatar: 'https://example.com/avatar.jpg',
        ));

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'name' => 'Mariano Demo',
            'username' => 'mariano',
            'email' => 'mariano@example.com',
            'google_id' => 'google-123',
            'avatar_url' => 'https://example.com/avatar.jpg',
            'auth_provider' => 'google',
        ]);

        $user = User::query()->where('email', 'mariano@example.com')->firstOrFail();
        $this->assertNotNull($user->password);
    }

    public function test_google_callback_links_existing_email_user_without_duplicate(): void
    {
        $this->configureGoogle();

        $user = User::factory()->create([
            'email' => 'ana@example.com',
            'google_id' => null,
            'avatar_url' => null,
            'auth_provider' => null,
        ]);

        $this->mockGoogleUser($this->googleUser(
            id: 'google-ana',
            name: 'Ana Google',
            email: 'ana@example.com',
            avatar: 'https://example.com/ana.jpg',
        ));

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user->fresh());
        $this->assertSame(1, User::query()->where('email', 'ana@example.com')->count());

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'ana@example.com',
            'google_id' => 'google-ana',
            'avatar_url' => 'https://example.com/ana.jpg',
            'auth_provider' => 'google',
        ]);
    }

    public function test_google_callback_handles_username_collision(): void
    {
        $this->configureGoogle();

        User::factory()->create(['username' => 'mariano']);
        User::factory()->create(['username' => 'mariano2']);

        $this->mockGoogleUser($this->googleUser(
            id: 'google-mariano',
            name: 'Mariano Nuevo',
            email: 'mariano@example.com',
        ));

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('users', [
            'email' => 'mariano@example.com',
            'username' => 'mariano3',
            'google_id' => 'google-mariano',
        ]);
    }

    public function test_google_callback_sends_code_when_google_email_is_explicitly_unverified(): void
    {
        $this->configureGoogle();
        $this->configureBrevo();

        $brevoPayloads = [];
        Http::fake([
            'https://api.brevo.com/v3/smtp/email' => function (Request $request) use (&$brevoPayloads) {
                $brevoPayloads[] = $request->data();

                return Http::response(['messageId' => 'brevo-message-id'], 201);
            },
        ]);

        $this->mockGoogleUser($this->googleUser(
            id: 'google-unverified',
            name: 'Correo Pendiente',
            email: 'pendiente@example.com',
            emailVerified: false,
        ));

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('verification.code.show'));

        $user = User::query()->where('email', 'pendiente@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertFalse($user->hasVerifiedEmail());
        Http::assertSentCount(1);
        $this->assertSame('pendiente@example.com', $brevoPayloads[0]['to'][0]['email']);
    }

    public function test_traditional_login_still_works(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    public function test_google_button_is_visible_only_when_configured(): void
    {
        config([
            'services.google.client_id' => null,
            'services.google.client_secret' => null,
            'services.google.redirect' => null,
        ]);

        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('Continuar con Google');

        $this->configureGoogle();

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Continuar con Google');
    }

    public function test_google_fields_are_nullable_for_existing_users(): void
    {
        $user = User::factory()->create([
            'google_id' => null,
            'avatar_url' => null,
            'auth_provider' => null,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'google_id' => null,
            'avatar_url' => null,
            'auth_provider' => null,
        ]);
    }

    private function configureGoogle(): void
    {
        config([
            'services.google.client_id' => 'client-id',
            'services.google.client_secret' => 'client-secret',
            'services.google.redirect' => 'http://localhost/auth/google/callback',
        ]);
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

    private function mockGoogleUser(SocialiteUser $user): void
    {
        $provider = Mockery::mock();
        $provider->shouldReceive('user')
            ->once()
            ->andReturn($user);

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($provider);
    }

    private function googleUser(
        string $id,
        string $name,
        string $email,
        ?string $avatar = null,
        bool $emailVerified = true,
    ): SocialiteUser
    {
        return (new SocialiteUser())->setRaw([
            'sub' => $id,
            'name' => $name,
            'email' => $email,
            'picture' => $avatar,
            'email_verified' => $emailVerified,
        ])->map([
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'avatar' => $avatar,
        ]);
    }
}
