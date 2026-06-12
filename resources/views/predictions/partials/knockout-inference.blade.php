{{-- Drives the knockout qualified-team selector from the predicted score.
     Empty score: no choice. Non-draw: auto-infers the score winner and hides the
     buttons. Draw: reveals the flag buttons so the user picks who advances.
     Server-side resolution remains authoritative; this is progressive enhancement. --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-qualified-selector]').forEach((selector) => {
            const card = selector.closest('[data-knockout-card]');

            if (! card) {
                return;
            }

            const scoreA = card.querySelector('[data-score-a]');
            const scoreB = card.querySelector('[data-score-b]');

            if (! scoreA || ! scoreB) {
                return;
            }

            const radios = selector.querySelectorAll('[data-qualified-radio]');
            const emptyHint = selector.querySelector('[data-qualified-empty]');
            const autoHint = selector.querySelector('[data-qualified-auto]');
            const autoName = selector.querySelector('[data-qualified-auto-name]');
            const drawBlock = selector.querySelector('[data-qualified-draw]');
            const teamA = selector.dataset.teamA;
            const teamB = selector.dataset.teamB;
            const teamAName = selector.dataset.teamAName ?? '';
            const teamBName = selector.dataset.teamBName ?? '';

            const update = () => {
                const a = parseInt(scoreA.value, 10);
                const b = parseInt(scoreB.value, 10);
                const hasBoth = Number.isInteger(a) && Number.isInteger(b);
                const isDraw = hasBoth && a === b;
                const isNonDraw = hasBoth && ! isDraw;

                selector.dataset.qualifiedState = hasBoth ? (isDraw ? 'draw' : 'auto') : 'empty';

                emptyHint?.toggleAttribute('hidden', hasBoth);
                autoHint?.toggleAttribute('hidden', ! isNonDraw);
                drawBlock?.toggleAttribute('hidden', ! isDraw);

                if (isNonDraw) {
                    const winner = a > b ? teamA : teamB;
                    radios.forEach((radio) => {
                        radio.checked = radio.value === winner;
                    });
                    if (autoName) {
                        autoName.textContent = a > b ? teamAName : teamBName;
                    }
                } else if (! isDraw) {
                    // Empty score: clear any stale selection so nothing is submitted.
                    radios.forEach((radio) => {
                        radio.checked = false;
                    });
                }
            };

            scoreA.addEventListener('input', update);
            scoreB.addEventListener('input', update);
            update();
        });
    });
</script>
