@props([
    'autoDismissMs' => 3800,
])

@php
    $statusMessages = [
        'profile-updated' => __('Perfil actualizado.'),
        'password-updated' => __('Contraseña actualizada.'),
        'verification-link-sent' => __('Te enviamos un nuevo enlace de verificación.'),
    ];

    $toastStyles = [
        'success' => [
            'container' => 'border-emerald-200 text-emerald-900 shadow-emerald-950/10 ring-emerald-100',
            'dot' => 'bg-emerald-500',
            'button' => 'text-emerald-700 hover:bg-emerald-50',
            'role' => 'status',
        ],
        'error' => [
            'container' => 'border-red-200 text-red-800 shadow-red-950/10 ring-red-100',
            'dot' => 'bg-red-500',
            'button' => 'text-red-700 hover:bg-red-50',
            'role' => 'alert',
        ],
        'warning' => [
            'container' => 'border-orange-200 text-orange-900 shadow-orange-950/10 ring-orange-100',
            'dot' => 'bg-orange-500',
            'button' => 'text-orange-700 hover:bg-orange-50',
            'role' => 'status',
        ],
        'info' => [
            'container' => 'border-blue-200 text-blue-900 shadow-blue-950/10 ring-blue-100',
            'dot' => 'bg-blue-500',
            'button' => 'text-blue-700 hover:bg-blue-50',
            'role' => 'status',
        ],
    ];

    $toasts = collect();

    foreach (['success', 'error', 'warning', 'info'] as $type) {
        if (session()->has($type)) {
            $toasts->push([
                'type' => $type,
                'message' => session($type),
            ]);
        }
    }

    if (session()->has('status')) {
        $status = session('status');

        $toasts->push([
            'type' => 'success',
            'message' => $statusMessages[$status] ?? $status,
        ]);
    }

    if ($errors->any()) {
        $toasts->push([
            'type' => 'error',
            'message' => $errors->first(),
        ]);
    }
@endphp

@if ($toasts->isNotEmpty())
    <div
        id="global-toasts"
        class="pointer-events-none fixed inset-x-0 top-4 z-50 mx-auto flex max-w-md flex-col gap-3 px-4 sm:right-4 sm:left-auto sm:mx-0 sm:px-0"
        data-toast-container
        data-auto-dismiss-ms="{{ $autoDismissMs }}"
    >
        @foreach ($toasts as $toast)
            @php
                $style = $toastStyles[$toast['type']] ?? $toastStyles['info'];
            @endphp

            <div
                class="global-toast pointer-events-auto flex items-start gap-3 rounded-2xl border bg-white px-4 py-3 text-sm font-bold shadow-2xl ring-1 transition duration-200 {{ $style['container'] }}"
                role="{{ $style['role'] }}"
            >
                <span class="mt-1 h-2 w-2 shrink-0 rounded-full {{ $style['dot'] }}"></span>
                <p class="flex-1">{{ $toast['message'] }}</p>
                <button
                    type="button"
                    class="-mr-1 rounded-full px-2 text-lg leading-none {{ $style['button'] }}"
                    data-toast-dismiss
                    aria-label="{{ __('Cerrar') }}"
                >
                    &times;
                </button>
            </div>
        @endforeach
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.querySelector('[data-toast-container]');
            const autoDismissMs = Number(container?.dataset.autoDismissMs || 3800);

            document.querySelectorAll('.global-toast').forEach((toast) => {
                const dismiss = () => {
                    toast.classList.add('opacity-0', 'translate-y-[-0.5rem]');
                    window.setTimeout(() => toast.remove(), 200);
                };

                toast.querySelector('[data-toast-dismiss]')?.addEventListener('click', dismiss);
                window.setTimeout(dismiss, autoDismissMs);
            });
        });
    </script>
@endif
