<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\EmailVerificationCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request, EmailVerificationCodeService $verificationCodes): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        try {
            $verificationCodes->sendCode($request->user());

            return back()->with('status', 'verification-code-sent');
        } catch (Throwable $exception) {
            Log::error('Failed to resend verification notification code.', [
                'user_id' => $request->user()->id,
                'exception' => $exception,
            ]);

            return back()->with('error', __('No pudimos reenviar el código de verificación. Probá de nuevo en unos minutos.'));
        }
    }
}
