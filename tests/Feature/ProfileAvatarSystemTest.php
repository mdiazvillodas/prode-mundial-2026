<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\ProfileAvatarCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use InvalidArgumentException;
use Tests\TestCase;

class ProfileAvatarSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_allows_users_with_null_profile_avatar_key(): void
    {
        $user = User::factory()->create([
            'profile_avatar_key' => null,
        ]);

        $this->assertNull($user->profile_avatar_key);
        $this->assertFalse($user->hasChosenProfileAvatar());
        $this->assertSame('default', $user->profileAvatar()['key']);
    }

    public function test_user_can_store_a_valid_avatar_key(): void
    {
        $user = User::factory()->create();

        $user->setProfileAvatarKey('avatar-03')->save();

        $user->refresh();

        $this->assertSame('avatar-03', $user->profile_avatar_key);
        $this->assertTrue($user->hasChosenProfileAvatar());
        $this->assertSame('Avatar 3', $user->profileAvatarLabel());
        $this->assertStringEndsWith('/avatars/3.png', $user->profileAvatarUrl());
    }

    public function test_default_can_be_stored_as_an_explicit_avatar_choice(): void
    {
        $user = User::factory()->create();

        $user->setProfileAvatarKey('default')->save();

        $user->refresh();

        $this->assertSame('default', $user->profile_avatar_key);
        $this->assertTrue($user->hasChosenProfileAvatar());
        $this->assertSame('avatars/0.png', $user->profileAvatar()['path']);
    }

    public function test_invalid_avatar_keys_are_rejected_by_user_helper(): void
    {
        $user = User::factory()->create();

        $this->expectException(InvalidArgumentException::class);

        $user->setProfileAvatarKey('avatar-10');
    }

    public function test_user_helper_returns_default_avatar_when_key_is_null_or_invalid(): void
    {
        $nullUser = User::factory()->create([
            'profile_avatar_key' => null,
        ]);
        $invalidUser = User::factory()->create([
            'profile_avatar_key' => 'not-in-catalog',
        ]);

        $this->assertSame(ProfileAvatarCatalog::default()['path'], $nullUser->profileAvatar()['path']);
        $this->assertSame(ProfileAvatarCatalog::default()['path'], $invalidUser->profileAvatar()['path']);
        $this->assertFalse($invalidUser->hasChosenProfileAvatar());
    }

    public function test_profile_avatar_component_renders_without_broken_paths(): void
    {
        $nullUser = User::factory()->make([
            'name' => 'Sin elegir',
            'profile_avatar_key' => null,
        ]);
        $defaultUser = User::factory()->make([
            'name' => 'Default User',
            'profile_avatar_key' => 'default',
        ]);
        $avatarUser = User::factory()->make([
            'name' => 'Avatar User',
            'profile_avatar_key' => 'avatar-09',
        ]);

        $nullHtml = Blade::render('<x-profile-avatar :user="$user" />', ['user' => $nullUser]);
        $defaultHtml = Blade::render('<x-profile-avatar :user="$user" />', ['user' => $defaultUser]);
        $avatarHtml = Blade::render('<x-profile-avatar :user="$user" size="lg" />', ['user' => $avatarUser]);

        $this->assertStringContainsString('/avatars/0.png', $nullHtml);
        $this->assertStringContainsString('/avatars/0.png', $defaultHtml);
        $this->assertStringContainsString('/avatars/9.png', $avatarHtml);
        $this->assertStringContainsString('Avatar de Avatar User', $avatarHtml);
        $this->assertStringNotContainsString('/avatars/10.png', $avatarHtml);
    }
}
