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

- Dashboard.
- Inline predictions page.
- Matches page.
- Calendar.
- Leaderboard.
- Private leagues.

## Product Language

Use product language centered on sports prediction and points:

- prediccion
- puntos
- ranking
- liga
- partido

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
