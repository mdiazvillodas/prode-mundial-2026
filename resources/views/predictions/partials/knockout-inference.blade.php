{{-- Infers the qualified team from the predicted score for knockout cards.
     Non-draw: auto-selects the score winner. Draw: prompts an explicit choice.
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
            const autoHint = selector.querySelector('[data-qualified-auto]');
            const drawHint = selector.querySelector('[data-qualified-draw]');
            const teamA = selector.dataset.teamA;
            const teamB = selector.dataset.teamB;

            const update = () => {
                const a = parseInt(scoreA.value, 10);
                const b = parseInt(scoreB.value, 10);
                const hasBoth = Number.isInteger(a) && Number.isInteger(b);
                const isDraw = hasBoth && a === b;

                if (hasBoth && ! isDraw) {
                    const winner = a > b ? teamA : teamB;
                    radios.forEach((radio) => {
                        radio.checked = radio.value === winner;
                    });
                    autoHint?.removeAttribute('hidden');
                    drawHint?.setAttribute('hidden', '');
                } else {
                    autoHint?.setAttribute('hidden', '');

                    if (isDraw) {
                        drawHint?.removeAttribute('hidden');
                    } else {
                        drawHint?.setAttribute('hidden', '');
                    }
                }
            };

            scoreA.addEventListener('input', update);
            scoreB.addEventListener('input', update);
            update();
        });
    });
</script>
