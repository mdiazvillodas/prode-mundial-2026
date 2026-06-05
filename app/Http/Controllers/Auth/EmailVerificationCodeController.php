<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\EmailVerificationCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class EmailVerificationCodeController extends Controller
{
    public function show(Request $request): RedirectResponse|View
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        return view('auth.verify-code');
    }

    public function verify(Request $request, EmailVerificationCodeService $verificationCodes): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'digits:6'],
        ], [
            'code.required' => __('Ingresá el código de verificación.'),
            'code.digits' => __('El código debe tener 6 dígitos.'),
        ]);

        if (! $verificationCodes->verify($request->user(), $validated['code'])) {
            return back()
                ->withInput()
                ->withErrors(['code' => __('El código es inválido, ya fue usado o venció.')]);
        }

        return redirect()
            ->intended(route('dashboard', absolute: false))
            ->with('success', __('Correo verificado. Ya podés entrar a Mi Prode.'));
    }

    public function resend(Request $request, EmailVerificationCodeService $verificationCodes): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        try {
            $verificationCodes->sendCode($request->user());

            return back()->with('status', 'verification-code-sent');
        } catch (Throwable $exception) {
            Log::error('Failed to resend verification code.', [
                'user_id' => $request->user()->id,
                'exception' => $exception,
            ]);

            return back()->with('error', __('No pudimos reenviar el código de verificación. Probá de nuevo en unos minutos.'));
        }
    }
}
