<?php

namespace Tests\Unit;

use App\Support\ProfileAvatarCatalog;
use Tests\TestCase;

class ProfileAvatarCatalogTest extends TestCase
{
    public function test_config_returns_all_avatar_options(): void
    {
        $avatars = ProfileAvatarCatalog::all();

        $this->assertCount(10, $avatars);
        $this->assertSame([
            'default',
            'avatar-01',
            'avatar-02',
            'avatar-03',
            'avatar-04',
            'avatar-05',
            'avatar-06',
            'avatar-07',
            'avatar-08',
            'avatar-09',
        ], $avatars->keys()->values()->all());
    }

    public function test_default_avatar_exists(): void
    {
        $default = ProfileAvatarCatalog::default();

        $this->assertSame('default', $default['key']);
        $this->assertSame('Sin avatar', $default['label']);
        $this->assertSame('avatars/0.png', $default['path']);
        $this->assertFileExists(public_path($default['path']));
    }

    public function test_all_configured_avatar_asset_files_exist(): void
    {
        foreach (ProfileAvatarCatalog::all() as $avatar) {
            $this->assertStringStartsWith('avatars/', $avatar['path']);
            $this->assertStringEndsWith('.png', $avatar['path']);
            $this->assertFileExists(public_path($avatar['path']), "{$avatar['key']} asset is missing.");
        }
    }

    public function test_valid_keys_are_accepted_and_invalid_keys_are_rejected(): void
    {
        $this->assertTrue(ProfileAvatarCatalog::isValid('default'));
        $this->assertTrue(ProfileAvatarCatalog::isValid('avatar-01'));
        $this->assertTrue(ProfileAvatarCatalog::isValid('avatar-09'));

        $this->assertFalse(ProfileAvatarCatalog::isValid(null));
        $this->assertFalse(ProfileAvatarCatalog::isValid(''));
        $this->assertFalse(ProfileAvatarCatalog::isValid('avatar-10'));
        $this->assertFalse(ProfileAvatarCatalog::isValid('google'));
    }

    public function test_avatar_path_and_url_helpers_return_public_values(): void
    {
        $this->assertSame('avatars/1.png', ProfileAvatarCatalog::path('avatar-01'));
        $this->assertStringEndsWith('/avatars/1.png', ProfileAvatarCatalog::url('avatar-01'));
        $this->assertNull(ProfileAvatarCatalog::path('missing-avatar'));
        $this->assertNull(ProfileAvatarCatalog::url('missing-avatar'));
    }
}
