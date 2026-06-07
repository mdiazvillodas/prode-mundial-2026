<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileAvatarPromptTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_null_profile_avatar_key_can_see_avatar_prompt_on_dashboard(): void
    {
        $user = User::factory()->create([
            'profile_avatar_key' => null,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Elegí tu avatar')
            ->assertSee('Guardar y continuar')
            ->assertSee('Sin avatar')
            ->assertSee('Avatar 9');
    }

    public function test_user_with_default_profile_avatar_key_does_not_see_prompt(): void
    {
        $user = User::factory()->create([
            'profile_avatar_key' => 'default',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Elegí tu avatar');
    }

    public function test_user_with_valid_profile_avatar_key_does_not_see_prompt(): void
    {
        $user = User::factory()->create([
            'profile_avatar_key' => 'avatar-04',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Elegí tu avatar');
    }

    public function test_authenticated_user_can_update_avatar_to_default(): void
    {
        $user = User::factory()->create([
            'profile_avatar_key' => null,
        ]);

        $this->actingAs($user)
            ->from(route('dashboard'))
            ->patch(route('profile.avatar.update'), [
                'profile_avatar_key' => 'default',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('dashboard'));

        $this->assertSame('default', $user->refresh()->profile_avatar_key);
    }

    public function test_authenticated_user_can_update_avatar_to_valid_key(): void
    {
        $user = User::factory()->create([
            'profile_avatar_key' => null,
        ]);

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->patch(route('profile.avatar.update'), [
                'profile_avatar_key' => 'avatar-07',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $this->assertSame('avatar-07', $user->refresh()->profile_avatar_key);
    }

    public function test_invalid_avatar_key_is_rejected(): void
    {
        $user = User::factory()->create([
            'profile_avatar_key' => null,
        ]);

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->patch(route('profile.avatar.update'), [
                'profile_avatar_key' => 'avatar-10',
            ])
            ->assertSessionHasErrors('profile_avatar_key')
            ->assertRedirect(route('profile.edit'));

        $this->assertNull($user->refresh()->profile_avatar_key);
    }

    public function test_guests_cannot_update_avatar(): void
    {
        $this->patch(route('profile.avatar.update'), [
            'profile_avatar_key' => 'default',
        ])->assertRedirect(route('login'));
    }

    public function test_user_can_access_profile_avatar_change_section(): void
    {
        $user = User::factory()->create([
            'profile_avatar_key' => 'avatar-02',
        ]);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Actualizar avatar')
            ->assertSee('Avatar')
            ->assertSee('Avatar 2')
            ->assertSee('/avatars/2.png', false);
    }
}
