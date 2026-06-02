# Prode Mundial 2026 - Design System

Last updated: 2026-06-02

This document is the design system foundation for Prode Mundial 2026. Future UI work must read this file before editing Blade views, Tailwind classes, or frontend assets.

## Current Status

The product owner prompt referenced design tokens, but the actual token values were not included. No new colors, typography, spacing scale, shadows, or Tailwind tokens should be invented until those values are provided.

All sections below define the required token categories and current implementation status. Values marked `pending decision` must be supplied or explicitly approved before they are added to `tailwind.config.js` or applied across screens.

## Principles

- Mobile-first.
- Modern sports prediction platform.
- Competitive but friendly.
- Clear hierarchy for partido, prediccion, puntos, ranking, liga, team names, scores, statuses, and actions.
- Avoid gambling or betting language.
- Avoid copying protected FIFA branding, official marks, logos, mascots, typography, or tournament identity.
- Keep Blade, Tailwind CSS, and vanilla JavaScript.

## Typography

### Font Family

- Primary font: pending decision.
- Secondary font: pending decision.
- Numeric / score font: pending decision.
- Web font loading strategy: pending decision.

Do not commit proprietary or licensed font files. If a web font is approved later, load it through a safe external stylesheet or documented deployment strategy.

### Font Weights

- Regular: pending decision.
- Medium: pending decision.
- Semibold: pending decision.
- Bold / display: pending decision.

### Heading Scale

- Page title: pending decision.
- Section title: pending decision.
- Card title: pending decision.
- Compact metadata title: pending decision.

### Body Text Scale

- Body: pending decision.
- Small body: pending decision.
- Caption / metadata: pending decision.
- Form helper text: pending decision.

### Numeric And Score Styling

- Match scores should be visually prominent and easy to scan.
- Prediction score inputs should be compact, touch-friendly, and visually paired with the two teams.
- Ranking points should use a stronger numeric treatment than surrounding metadata.
- Exact font size, weight, spacing, and color tokens: pending decision.

## Color Tokens

### Brand Palette

- Brand primary: pending decision.
- Brand secondary: pending decision.
- Brand accent: pending decision.
- Brand dark: pending decision.
- Brand light: pending decision.

### Neutral Palette

- Background: pending decision.
- Surface: pending decision.
- Surface muted: pending decision.
- Border: pending decision.
- Text primary: pending decision.
- Text secondary: pending decision.
- Text muted: pending decision.

### Semantic Colors

- Success / saved: pending decision.
- Warning / pending: pending decision.
- Error / validation: pending decision.
- Info / neutral notice: pending decision.
- Open / predictable: pending decision.
- Locked / closed: pending decision.
- Finished: pending decision.
- Placeholder: pending decision.

## Semantic Usage

- Open or predictable matches must make the action available and obvious.
- Locked, closed, finished, and placeholder matches must remain readable but clearly non-editable.
- Saved states should feel calm and reliable.
- Validation errors should be visible near the related input or action.
- Admin and destructive actions must use distinct, cautious styling.

Exact semantic token mapping is pending decision.

## Spacing And Layout

### Spacing Scale

- Base spacing unit: pending decision.
- Card padding mobile: pending decision.
- Card padding desktop: pending decision.
- Section gap: pending decision.
- Form field gap: pending decision.
- Dense metadata gap: pending decision.

### Layout Rules

- Build mobile-first.
- Prefer single-column layouts on small screens.
- Use compact but readable cards for match-heavy screens.
- Avoid nested cards.
- Use constrained content widths for dashboards and admin pages.
- Keep interactive controls touch-friendly.

Exact breakpoints and container widths should follow Tailwind defaults until product-approved tokens say otherwise.

## Border Radius

- Small radius: pending decision.
- Card radius: pending decision.
- Button radius: pending decision.
- Input radius: pending decision.
- Badge radius: pending decision.

Existing guidance: cards should generally stay at 8px radius or less unless the design system later approves a different token.

## Shadows

- Card shadow: pending decision.
- Raised action shadow: pending decision.
- Floating save action shadow: pending decision.
- Modal / overlay shadow: pending decision.

Use shadows sparingly. Screens should feel clean and sports-focused, not heavy or decorative.

## Buttons

### Primary Button

- Background: pending decision.
- Text: pending decision.
- Hover: pending decision.
- Focus ring: pending decision.
- Disabled: pending decision.

### Secondary Button

- Background: pending decision.
- Border: pending decision.
- Text: pending decision.
- Hover: pending decision.
- Focus ring: pending decision.

### Destructive Button

- Background / border: pending decision.
- Text: pending decision.
- Hover: pending decision.
- Focus ring: pending decision.

Button labels should be direct and action-oriented.

## Inputs

- Text input background: pending decision.
- Text input border: pending decision.
- Focus border / ring: pending decision.
- Disabled state: pending decision.
- Error state: pending decision.
- Score input dimensions: pending decision.

Score inputs must remain readable and easy to tap on mobile.

## Cards

- Match card background: pending decision.
- Match card border: pending decision.
- Dashboard card background: pending decision.
- Admin card background: pending decision.
- Leaderboard row background: pending decision.
- First-place highlight: pending decision.

Cards should support quick scanning: teams, time, status, result, prediction inputs, and primary action.

## Badges And Status States

Required statuses:

- scheduled
- open
- locked
- finished
- placeholder
- submitted
- scored
- pending
- accepted
- rejected
- active
- removed
- saved
- validation error

For each status, the following tokens are pending decision:

- Background color.
- Text color.
- Border color.
- Icon usage, if any.

## Mobile-First Rules

- Start every UI layout at mobile width.
- Avoid horizontal overflow.
- Keep buttons and form controls touch-friendly.
- Keep match cards scannable without relying on hover.
- Use responsive grids only when desktop space helps comparison.
- Do not hide critical actions on mobile.

## Tailwind Configuration

Tailwind should not be changed until approved token values are provided.

Current status:

- No new Tailwind tokens were added in E11-T00.
- `tailwind.config.js` remains unchanged.
- Future token implementation should add only approved tokens.

## Pending Product Decisions

- Actual font family.
- Approved brand color palette.
- Neutral color scale.
- Semantic status colors.
- Spacing scale.
- Radius scale.
- Shadow scale.
- Button variants.
- Input variants.
- Badge/status variants.
- Score-specific typography.
