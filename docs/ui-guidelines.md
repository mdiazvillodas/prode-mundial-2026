# Prode Mundial 2026 - UI Guidelines

Last updated: 2026-05-31

These guidelines define the visual direction for the Prode Mundial 2026 platform. They are based on the current Prode mockup/reference direction and should guide future UI work.

For concrete design-system categories and token status, read `docs/design-system.md` before UI implementation. If a token is marked `pending decision`, do not invent a replacement without product approval.

The Prode prediction mock is the visual source for the first UI polish pass. Future work should use the mock-derived hierarchy and components documented in `docs/design-system.md`, starting with the `/predictions` screen.

## Direction

- Build mobile-first.
- The product should feel like a modern sports prediction platform.
- Use a competitive but friendly tone.
- Bring FIFA World Cup inspired energy without copying protected branding, official marks, logos, mascots, typography, or tournament identity.
- Avoid the generic Laravel Breeze look as the product UI matures.
- Use `public/brand/p26-logo.svg` as the visible Prode/P26 brand mark and `public/brand/favicon.ico` as the browser icon.
- Keep Blade, Tailwind CSS, and vanilla JavaScript.
- Use the mock-derived design system as the source of truth for app shell, match cards, score inputs, status badges, floating save actions, and mobile hierarchy.

## Layout Principles

- Prioritize strong match-card layouts.
- Make hierarchy clear for:
  - teams
  - scores
  - match status
  - prediction inputs
  - primary actions
- Use clean cards, rounded corners, soft shadows, generous spacing, and clear CTAs.
- Keep dense information readable on mobile.
- Cards should support quick scanning: who plays, when, status, current result, and what action is available.
- Use compact but clear date/day grouping for match-heavy screens.

## Important Screens

Design attention should focus first on:

- Inicio / Panel.
- Inline predictions page.
- Matches page.
- Calendar.
- Ligas.
- Ligas privadas.

Navigation direction:

- `Predicciones` is the primary match-action destination.
- `Ligas` is the primary ranking and league destination.
- The general ranking should be presented as the `Liga general`.
- Private league rankings should be presented as a `Ranking de la liga` or `Tabla de posiciones` inside each league.
- `Partidos` should not be a primary navigation item because it duplicates the match list already present in `Predicciones`.
- `/matches` may remain available for compatibility or internal access, but it should not compete as a main user destination.

Calendar direction:

- `Calendario` should be a team-focused schedule screen, not another generic match list.
- The user should choose a team and then see that team's known schedule.
- Calendar cards should show opponent, date, local time, group/stage, status, and result when finished.
- For v1, do not infer hypothetical knockout paths. Future knockout matches should appear for a team only after that team has been assigned to the match by admin or future API integration.
- If no team is selected, show a helpful empty state that invites the user to choose one.

## Product Language

Use product language centered on sports prediction and points:

- prediccion
- puntos
- ranking
- liga
- partido
- Liga general
- Liga privada
- Tabla de posiciones
- Solicitud de ingreso

Avoid gambling or betting language, including:

- apuesta
- apostar
- odds
- casino
- wager
- bet
- real-money prize language

## Match Cards

Match cards should make the core state obvious at a glance:

- Teams should be prominent.
- Scores and prediction inputs should be easy to locate.
- Match date/time should be visible but secondary.
- Stage and group should be compact metadata.
- Status should be shown with consistent labels and visual treatment.
- Primary actions should be obvious and touch-friendly.

## Knockout Qualified-Team Selector

Knockout matches reuse the normal score inputs plus a qualified-team selector (`<x-knockout-qualified-selector>`):

- Keep copy compact and mobile-friendly so the card stays short. The selector is driven by the predicted score and exposes three states via `data-qualified-state` (`empty` / `auto` / `draw`); the progressive-enhancement script (`predictions/partials/knockout-inference.blade.php`) keeps it in sync, and the same state is rendered server-side from the saved/old score so it is correct without JavaScript.
- Lead with one short line: "En eliminatorias, si pronosticás empate, elegí quién clasifica." Avoid long paragraphs and gambling language.
- Empty score (`empty`): do not allow choosing a qualified team. Hide the flag buttons and show a compact neutral helper ("Cargá el resultado para definir quién clasifica.").
- Non-draw score (`auto`): the qualified team is inferred from the score winner — never let the user pick the opposite team. Hide the flag buttons and show a compact "Clasifica automáticamente: <equipo>" line. The inferred team is still submitted so the saved data stays consistent; server-side resolution remains authoritative.
- Draw score (`draw`): only then reveal the flag/label radio buttons (not a `<select>`), mobile-first, with a clear selected state. A draw requires an explicit qualified-team choice; if the 120' clarification is kept, keep it secondary and short ("Cuenta el resultado final jugado, incluido alargue.").
- The qualified-team radios participate in the inline "unsaved changes" tracking so the floating save action behaves consistently.
- Closed/read-only knockout predictions must show both the predicted score and the predicted qualified team.

## Live Status Labels

- Never show technical live states (`1H`, `2H`, `HT`, `ET`, `LIVE`, `1T`, `2T`, …) to users. For any live-ish state, show the simple Spanish label "En vivo".
- This is presentation only — stored `api_status` values and API sync logic are unchanged. The user-facing label is centralized in `LiveDashboardDataService` (`status_label` / `dailyStatusLabel()` return "En vivo" for live statuses); views consuming it must not re-expose the raw `api_status`.
- Admin/debug/API-health views may still display the raw `api_status` for operational clarity.

## Visual States

Use distinct visual treatments for:

- Open / predictable: available for prediction, clear CTA or enabled inputs.
- Locked / closed: visible but not editable.
- Finished: result is emphasized; prediction inputs are no longer editable.
- Placeholder: teams are not known yet; make this explicit.
- Saved: show a calm success state.
- Validation error: show clear field-level feedback and avoid ambiguous messages.

## Team Visuals

- Reserve space in match cards for future team flags or logos.
- Use `country_code` as the current fallback when a visual identifier is needed.
- Do not integrate an external flag or logo API yet.
- Avoid adding official federation, FIFA, or protected marks unless properly licensed.

## CTAs And Interaction

- Primary actions should use clear, direct labels.
- Inline prediction screens should support saving multiple changed predictions at once.
- Floating save actions should appear only when there are unsaved changes.
- Disabled or read-only states should still explain why a match cannot be edited.

## Tone

- The UI should feel energetic, sports-focused, and social.
- Keep copy short and useful.
- Encourage participation through prediction, points, ranking, and league language.
- Do not imply betting, money, odds, or wagering.
