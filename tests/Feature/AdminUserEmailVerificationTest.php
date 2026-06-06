<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserEmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_users_page_and_see_verification_status(): void
    {
        $admin = User::factory()->admin()->create();
        $verified = User::factory()->create([
            'name' => 'Verified User',
            'username' => 'verified_user',
            'email' => 'verified@example.com',
            'google_id' => 'google-verified',
            'auth_provider' => 'google',
        ]);
        $unverified = User::factory()->unverified()->create([
            'name' => 'Pending User',
            'username' => 'pending_user',
            'email' => 'pending@example.com',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Admin usuarios')
            ->assertSee('Verified User')
            ->assertSee('@verified_user')
            ->assertSee('verified@example.com')
            ->assertSee('Verificado')
            ->assertSee('Vinculado')
            ->assertSee('Pending User')
            ->assertSee('@pending_user')
            ->assertSee('pending@example.com')
            ->assertSee('Pendiente')
            ->assertSee('Verificar email');

        $this->assertTrue($verified->hasVerifiedEmail());
        $this->assertFalse($unverified->hasVerifiedEmail());
    }

    public function test_normal_user_cannot_access_users_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_guest_cannot_access_users_page(): void
    {
        $this->get(route('admin.users.index'))
            ->assertRedirect('/login');
    }

    public function test_admin_can_manually_verify_unverified_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->unverified()->create();

        $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->patch(route('admin.users.verify-email', $user))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('status', 'Email verificado manualmente.');

        $this->assertTrue($user->refresh()->hasVerifiedEmail());

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_normal_user_cannot_manually_verify_another_user(): void
    {
        $user = User::factory()->create();
        $target = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->patch(route('admin.users.verify-email', $target))
            ->assertForbidden();

        $this->assertFalse($target->refresh()->hasVerifiedEmail());
    }

    public function test_guest_cannot_manually_verify_user(): void
    {
        $target = User::factory()->unverified()->create();

        $this->patch(route('admin.users.verify-email', $target))
            ->assertRedirect('/login');

        $this->assertFalse($target->refresh()->hasVerifiedEmail());
    }

    public function test_manual_verify_already_verified_user_is_friendly_and_idempotent(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $verifiedAt = $user->email_verified_at;

        $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->patch(route('admin.users.verify-email', $user))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('status', 'El usuario ya tenía el email verificado.');

        $user->refresh();

        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertTrue($verifiedAt->equalTo($user->email_verified_at));
    }
}
