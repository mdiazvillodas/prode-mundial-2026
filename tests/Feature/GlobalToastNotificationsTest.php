<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalToastNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_layout_renders_status_flash_as_global_toast(): void
    {
        $this->withSession(['status' => 'verification-code-sent'])
            ->get(route('login'))
            ->assertOk()
            ->assertSee('global-toasts')
            ->assertSee('Te enviamos un nuevo código de verificación.');
    }

    public function test_app_layout_renders_success_flash_as_global_toast(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['success' => 'Predicción guardada.'])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('global-toasts')
            ->assertSee('Predicción guardada.');
    }

    public function test_app_layout_renders_validation_errors_as_global_toast(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->patch(route('profile.update'), [
                'name' => '',
                'email' => 'invalid-email',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors(['name', 'email']);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('global-toasts');
    }
}
