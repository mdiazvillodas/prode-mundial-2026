# Prode Mundial 2026 - Design System

Last updated: 2026-06-02

This document is the visual system foundation for Prode Mundial 2026. Future UI work must read this file before editing Blade views, Tailwind classes, or frontend assets.

The current visual source is the Prode prediction mock. It defines the intended direction for the primary `/predictions` screen and should guide the first UI polish pass. Exact token values remain pending where they were not explicitly provided by product.

## Visual Identity Summary

- Premium mobile sports prediction app.
- Light app background with a subtle blue atmosphere.
- Strong blue/navy structure inspired by international football energy, without copying protected FIFA branding, marks, logos, mascots, typography, or tournament identity.
- White rounded cards with soft shadows and subtle borders.
- Clean, high-contrast hierarchy for partido, predicción, pronóstico, puntos, liga, ranking, teams, scores, statuses, and actions.
- Friendly and competitive tone.
- Entertainment only. Never imply gambling, betting, odds, wagers, cash prizes, or real-money mechanics.

## Brand Assets

Approved brand assets live in `public/brand/`:

- Main visible brand mark: `public/brand/p26-logo.svg`.
- Browser icon: `public/brand/favicon.ico`.

Usage rules:

- Reference the logo from Blade with `asset('brand/p26-logo.svg')`; do not inline the SVG unless a future ticket explicitly requires it.
- Reference the favicon in active base layouts with `asset('brand/favicon.ico')`.
- Use meaningful image alt text such as `Prode` or `Logo de Prode`.
- Keep the logo responsive with fixed height and automatic width so the artwork is not distorted.
- In authenticated navigation, the logo should identify the app without competing with page content.
- In guest/auth screens, the logo may be more prominent but should remain secondary to the form task.
- Do not modify the SVG or favicon files unless a brand asset update is explicitly requested.

## Typography

### Font Direction

- Primary recommendation: Plus Jakarta Sans.
- Acceptable alternative: Inter.
- Use a public web font only if it is safely available.
- Do not commit proprietary or licensed font files.
- If the exact official/custom font becomes legally available later, document it as a future replacement option before implementing.

### Usage

- Page titles: large, bold, readable on mobile.
- Section titles: semibold to bold, compact enough for dense match screens.
- UI labels and metadata: medium weight, short, and scannable.
- Team names: strong weight; uppercase only where it improves scanning.
- Scores, times, and ranking points: strong numeric treatment using the same font family with heavier weight unless a better public numeric font is approved later.
- Body text: avoid condensed or aggressive sports fonts; prioritize mobile readability.

### Pending Decisions

- Exact font family final choice.
- Exact heading scale.
- Exact body text scale.
- Exact score/numeric size and weight.

## Color System

Exact HEX values are pending decision. Use the semantic roles below as the implementation direction until approved values are supplied.

### Brand And Structure

- Primary blue: main active state, primary CTA, focused controls, selected navigation, saved confirmation when appropriate.
- Deep navy text: page titles, important labels, app shell text, strong card headings.
- Light app background: overall screen background, with subtle blue atmosphere rather than plain Breeze gray.
- Card background: white.
- Muted text: secondary copy, metadata, helper text.
- Border/subtle divider: soft card borders, separators, grouped metadata.
- White: cards, top surfaces, contrast text on dark/blue backgrounds.

### Semantic States

- Open/success green: predictable matches, access approved, positive saved states.
- Urgent/closing orange: closing soon, pending action, warning state.
- Placeholder blue: teams or knockout slots not defined yet.
- Finished/disabled gray: finished matches, locked/disabled controls, read-only state.
- Error red: validation errors and destructive warnings.

### Pending Decisions

- Exact HEX values for each semantic role.
- Hover/focus variants.
- Dark text contrast values.
- Approved status badge palette.

## App Shell

Target app feel:

- Mobile-first shell with a polished app-like frame.
- Top app bar with:
  - menu icon or compact navigation trigger
  - centered brand/logo area
  - user/avatar or account area
- Bottom navigation for primary user sections:
  - Inicio
  - Predicciones
  - Ligas
  - Historial
- `Predicciones` should be treated as the primary active destination for the v1 user experience.
- `Ligas` should be treated as the primary ranking and league destination.
- Do not expose `Ranking` as a competing primary navigation item when `Ligas` already covers the league and ranking experience.
- Desktop navigation can expand, but should keep the same hierarchy and avoid a crowded Breeze-style link row.

## Predictions Page Structure

The `/predictions` screen is the first implementation target for visual polish.

Target hierarchy:

- Top app bar.
- Page header with title `Predicciones`.
- Short explanatory subtitle.
- Secondary `Calendario` action.
- Date/filter pill row:
  - Hoy
  - Mañana
  - Esta semana
  - Todos
- Status legend:
  - Abierto
  - Cierra pronto
  - Equipos por definir
  - Finalizado
- Day section header:
  - date title
  - number of matches
- Match cards grouped by day.

## Match Card Component

Target structure:

- White rounded card.
- Soft shadow and subtle border.
- Status badge top-left.
- Optional chevron/action top-right.
- Time and group/stage shown as compact centered metadata.
- Team A on the left and Team B on the right.
- Circular flag/avatar space or country-code fallback.
- Score inputs centered and visually prominent.
- Status/feedback row below score:
  - `Tu predicción guardada`
  - `Editar hasta HH:mm`
  - `Se cierra en MM:SS`
  - `Equipos por definir`
  - `Finalizado`
- For knockout matches with teams, include the qualified-team selector inside the card.

Cards should make the main state obvious at a glance: who plays, when, whether it can be predicted, what the current prediction is, and whether there are unsaved changes.

## Semantic group and phase colors

Match-card accent colors must be semantic. Do not assign random colors per match.

For group-stage matches, the card accent color comes from the match group. The group color should be used consistently in:

- the left group band
- group badge or label
- subtle background texture/accent
- small UI accents where appropriate

The color identifies the match context, not the teams and not the individual match. Team colors should not override group colors in match-card structure.

If the group is missing or unknown, use the default neutral/navy style.

For knockout matches, use a neutral knockout / fase eliminatoria style unless a specific phase color map is later defined. Do not invent random phase colors. Use phase labels such as `Octavos`, `Cuartos`, `Semifinal`, and `Final`.

Initial group color map:

- Grupo A: green.
- Grupo B: blue.
- Grupo C: red.
- Grupo D: yellow/gold.
- Grupo E: navy/deep blue.
- Grupo F: purple or violet — pending exact token.
- Grupo G: orange — pending exact token.
- Grupo H: teal/cyan — pending exact token.

Exact HEX values are still pending unless already defined elsewhere in this document or a future approved token update.

Implementation note:

Future match-card UI work should expose a simple helper or mapping for group colors, for example by group letter/name, instead of scattering conditional Tailwind classes across many views. Keep the implementation simple and compatible with Blade + Tailwind.

## Score Input Component

- Large numeric inputs.
- Clear paired layout: team A score - team B score.
- Strong focus state using primary blue.
- Touch-friendly tap targets on mobile.
- Avoid tiny controls.
- Keep labels close to the relevant team.
- Existing values should feel intentionally pre-filled, not like placeholder text.
- Validation errors should appear near the affected input.

## Status Badges

Semantic badge direction:

- Abierto: green dot or green soft pill.
- Cierra pronto: orange dot or orange soft pill.
- Equipos por definir: blue dot or light blue soft pill.
- Finalizado: gray dot or gray soft pill.
- Guardado: blue check or calm confirmation text.
- Error de validación: red text/pill near the related action or field.

Exact background, text, border, and icon values are pending decision.

## Floating Save Button

- Appears only when there are unsaved changes.
- Fixed near the bottom of the mobile viewport, above bottom navigation.
- Strong primary blue or green CTA.
- Mobile-friendly height and width.
- Clear copy states:
  - `Guardar cambios`
  - `Guardando...`
  - `Cambios guardados`
- Should not hide important form inputs on small screens.

## Placeholder And Knockout States

- Placeholder cards must look intentional, not broken.
- Use a shield/trophy placeholder icon or neutral emblem in future UI polish.
- Text examples:
  - `Ganador Grupo A`
  - `Tercero Grupo C/D/E`
  - `Equipos por definir`
- Do not show prediction inputs until teams are assigned.
- Knockout matches with teams must show a qualified-team selector.
- Placeholder state should use the placeholder blue semantic treatment, not only muted gray.

## Buttons And CTAs

- Primary CTA: strong filled blue or green.
- Secondary CTA: white or soft surface button with icon if useful.
- Destructive actions: cautious red treatment, and future confirmation behavior for private league/admin polish.
- Buttons should have generous radius and touch-friendly targets.
- Labels should be direct and Spanish-first.

Examples:

- `Guardar cambios`
- `Cargar predicciones`
- `Ver ligas`
- `Ver tabla`
- `Solicitar ingreso`
- `Guardar resultado`

## Cards And Surfaces

- Use white cards over the light app background.
- Use large radius, soft shadows, and subtle borders.
- Avoid dense table-like layouts on mobile.
- Avoid nested cards where a section layout would be clearer.
- Match-heavy screens should prioritize scan speed over decoration.
- Admin screens can be denser, but should keep clear action zones.

## Empty, Success, And Error States

- Empty states should explain what happens next and provide one clear action when available.
- Success states should be calm and reliable.
- Validation errors should be field-level where possible, plus a concise summary only when useful.
- Error copy should explain what the user can do next.

## Team Flag Or Code Display

- Reserve circular visual space for future flags/logos.
- Use `country_code` fallback for now.
- Do not integrate external flag/logo APIs yet.
- Do not use official federation, FIFA, or protected marks unless properly licensed.

## Copy Style

- UI copy should be Spanish-first.
- Use `Ligas` as the main navigation concept.
- Use `Liga general` for the global competition and `Liga privada` or `liga de amigos` for private leagues.
- Use `Ranking de la liga` or `Tabla de posiciones` inside league views.
- Avoid English terms in app-controlled UI such as `Owner`, `Register`, `Log Out`, and `Profile`.
- Avoid gambling/betting language.
- Prefer:
  - predicción
  - pronóstico
  - puntos
  - liga
  - ranking
  - Liga general
  - Liga privada
  - Tabla de posiciones
  - Solicitud de ingreso
  - partido
  - clasificado
  - resultado

## Implementation Notes For Future Tickets

- Do not redesign all screens at once.
- First implementation target: `/predictions`.
- Then Inicio, navigation, history, and leagues.
- Then private league screens.
- Then admin screens.
- Keep business logic unchanged during visual tickets.
- Do not add React, Vue, Inertia, or unnecessary frontend dependencies.
- Do not add Tailwind tokens until exact values are approved.

## Tailwind Configuration

Tailwind should not be changed until approved token values are provided.

Current status:

- No final HEX colors are approved.
- No final typography scale is approved.
- No final radius/shadow scale is approved.
- Future Tailwind configuration should add only approved tokens.

## Pending Product Decisions

- Exact HEX colors for brand and semantic states.
- Final official/custom font if legally available.
- Final public font choice between Plus Jakarta Sans and Inter.
- Exact typography scale.
- Exact spacing scale.
- Exact border radius values.
- Exact shadow values.
- Icon strategy for status badges, bottom navigation, and placeholder cards.
