<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->orderByDesc('created_at')
            ->orderBy('id')
            ->paginate(50);

        return view('admin.users.index', [
            'users' => $users,
        ]);
    }

    public function verifyEmail(User $user): RedirectResponse
    {
        if ($user->hasVerifiedEmail()) {
            return redirect()
                ->route('admin.users.index')
                ->with('status', __('El usuario ya tenía el email verificado.'));
        }

        $user->forceFill([
            'email_verified_at' => now(),
        ])->save();

        return redirect()
            ->route('admin.users.index')
            ->with('status', __('Email verificado manualmente.'));
    }
}
