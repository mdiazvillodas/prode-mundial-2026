<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <title>{{ __('Tu código de verificación de Mi Prode') }}</title>
    </head>
    <body style="margin: 0; background: #f8fafc; color: #0f172a; font-family: Arial, sans-serif;">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background: #f8fafc; padding: 24px;">
            <tr>
                <td align="center">
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 520px; background: #ffffff; border-radius: 18px; border: 1px solid #e2e8f0; padding: 28px;">
                        <tr>
                            <td>
                                <p style="margin: 0 0 10px; color: #1d4ed8; font-size: 12px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;">
                                    {{ __('Mi Prode') }}
                                </p>

                                <h1 style="margin: 0 0 18px; color: #172554; font-size: 24px; line-height: 1.2;">
                                    {{ __('Verificá tu correo') }}
                                </h1>

                                <p style="margin: 0 0 16px; font-size: 16px; line-height: 1.6;">
                                    {{ __('Hola :name', ['name' => $user->name]) }}
                                </p>

                                <p style="margin: 0 0 16px; font-size: 16px; line-height: 1.6;">
                                    {{ __('Tu código de verificación es:') }}
                                </p>

                                <p style="margin: 0 0 20px; color: #172554; font-size: 34px; font-weight: 800; letter-spacing: .18em;">
                                    {{ $code }}
                                </p>

                                <p style="margin: 0 0 12px; font-size: 15px; line-height: 1.6;">
                                    {{ __('Este código vence en 15 minutos.') }}
                                </p>

                                <p style="margin: 0; color: #475569; font-size: 14px; line-height: 1.6;">
                                    {{ __('Si no creaste una cuenta en Mi Prode, podés ignorar este mensaje.') }}
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
