# Staging QA Strategy

Last updated: 2026-06-05

This document defines the staging QA subproject for Prode Mundial 2026. It guides demo seed data, safe reset commands, simulated result updates, and Playwright end-to-end smoke tests.

## Purpose

Staging QA exists to validate the full product experience in a browser before production. It should cover:

- normal user flows
- admin flows
- predictions before results
- points and rankings after results
- private league flows
- visual and navigation smoke checks

The staging QA process should be repeatable, maintainable, and updated as the product changes. It should exercise flows both before and after simulated match results arrive.

## Environments

Keep environment boundaries explicit:

- Local commands affect only the local `.env` and local database.
- Railway staging commands affect only Railway staging variables and the Railway staging database.
- Production must not share staging data.
- Staging should use `APP_ENV=staging` and `APP_MODE=test`.
- Production should eventually use `APP_ENV=production` and `APP_MODE=live`.

Staging is allowed to use deterministic demo data. Production must use live data and must not depend on staging users, staging passwords, or staging reset flows.

## Safety Rules

- Demo reset and demo seed commands must only run in local, testing, or staging environments.
- Never run destructive demo commands in production or live mode.
- Any destructive command must check the environment before it deletes or resets data.
- Do not use real user data in staging demo seed data.
- Demo passwords are allowed only for local and staging test users.
- Demo credentials must never be reused from real accounts.
- Demo reset and result simulation commands fail loudly when `APP_ENV=production` or `APP_MODE=live`.

## Demo Data Strategy

The staging demo dataset includes enough data to exercise the whole v1 product:

- one tournament
- teams
- group-stage matches
- knockout placeholders
- at least one knockout match with teams assigned
- an admin demo user
- normal demo users
- private leagues
- active memberships
- a pending join request
- predictions
- some matches with no results
- some matches close to the prediction deadline
- some placeholder matches
- some finished matches with scored predictions
- controlled E20 knockout QA matches for open UX, closed/read-only display, FT, AET, PEN team A, and PEN team B settlement checks

The dataset should be deterministic so QA can rely on stable accounts, stable leagues, stable match states, and predictable expected rankings.

The demo fixture is controlled QA data. It should not be treated as an official final World Cup fixture unless an official source is explicitly documented in a future ticket.

## Demo Users

The staging demo seeder creates stable demo accounts:

- `admin@prode.test`
- `mariano@prode.test`
- `ana@prode.test`
- `juan@prode.test`
- `lucia@prode.test`
- `diego@prode.test`
- `sofia@prode.test`

Passwords should be safe demo-only values. They must never be reused from real accounts and should be documented only in staging/local setup notes, not in production-facing UI.

Current demo password for all demo accounts:

```text
password
```

## Demo Reset Command

Use the safe reset command to prepare local or Railway staging for Phase A QA:

```bash
php artisan demo:reset-staging --force
```

The command:

- runs `migrate:fresh --seed --force`
- runs `Database\Seeders\StagingDemoSeeder`
- prepares the pre-results QA state
- refuses to run when `APP_ENV=production`
- refuses to run when `APP_MODE=live`
- is intended only for local, testing, and staging

For local manual use, `--force` may be omitted to get an interactive confirmation:

```bash
php artisan demo:reset-staging
```

For Railway staging, run the command in the Railway staging environment using the Railway shell or command runner:

```bash
php artisan demo:reset-staging --force
```

Before running it in Railway, verify the service variables are set for staging:

- `APP_ENV=staging`
- `APP_MODE=test`

Never run this command in production or live mode.

## Current Seeded QA Data

`Database\Seeders\StagingDemoSeeder` creates or updates:

- `FIFA World Cup 2026`
- a useful team set including Argentina, Brazil, France, Spain, Uruguay, United States, Germany, Mexico, England, and Japan
- open group-stage matches
- a scheduled group-stage match
- a locked group-stage match
- a close-to-deadline open match
- two finished group-stage matches with scored predictions
- one knockout placeholder
- one assigned knockout match with a qualified-team prediction path
- E20 knockout QA fixtures:
  - `E20 knockout QA open UX`
  - `E20 knockout QA closed read-only`
  - `E20 knockout QA FT 2-1`
  - `E20 knockout QA AET 2-1`
  - `E20 knockout QA PEN team A`
  - `E20 knockout QA PEN team B`
- demo users listed above
- `Liga Demo Palermo`, owned by `mariano@prode.test`
- active memberships for Mariano, Ana, Juan, Lucía, and Diego
- a pending join request for Sofía
- pending predictions for open/future matches
- scored predictions for finished matches

The seeder uses `updateOrCreate` where practical so it can be run repeatedly without uncontrolled duplicates. It does not call `migrate:fresh`, truncate tables, or connect to external APIs.

## QA Scenario Phases

### Phase A - Pre-Results State

Validate the product before simulated results are applied:

- users can log in
- predictions are visible
- open matches can be predicted
- locked, placeholder, and finished matches behave correctly
- private leagues can be created, searched, and joined
- rankings load with initial data
- navigation works on desktop and mobile

### Phase B - Simulated Results

The `demo:simulate-results` command simulates API-like result arrival:

- update match scores
- set `winner_team_id`
- mark matches as finished
- run settlement and scoring
- update prediction points

This phase should behave like the future external fixture/result API integration, while staying deterministic and safe for staging.

### Phase C - Post-Results State

Validate the product after simulated results have been applied:

- prediction history shows awarded points
- Liga general ranking updates
- private league rankings update
- finished matches are locked
- admin can correct results
- rescoring remains idempotent

## Result Simulation Command

Current command:

```bash
php artisan demo:simulate-results --scenario=group-day-1 --force
```

Knockout QA command:

```bash
php artisan demo:simulate-results --scenario=knockout-qa --force
```

The command should behave like the future API integration:

- apply result data to selected matches
- set `winner_team_id`
- mark matches as finished
- call existing settlement/scoring logic
- avoid duplicating points
- be safe for repeated use where possible
- block execution in production/live

For local manual use, `--force` may be omitted to get an interactive confirmation:

```bash
php artisan demo:simulate-results --scenario=group-day-1
```

For Railway staging, run the selected scenario in the Railway staging environment using the Railway shell or command runner:

```bash
php artisan demo:simulate-results --scenario=group-day-1 --force
```

```bash
php artisan demo:simulate-results --scenario=knockout-qa --force
```

The current `group-day-1` scenario applies deterministic QA results to known demo matches created by `StagingDemoSeeder`, including group-stage matches and the assigned knockout demo match. It is not an official result feed and does not connect to external APIs.

The `knockout-qa` scenario applies deterministic finished results to the E20 knockout QA fixtures:

- FT non-draw: Argentina 2-1 Brazil, Argentina qualifies.
- AET non-draw: France 2-1 Uruguay, France qualifies.
- PEN team A: Germany 1-1 Mexico, Germany qualifies.
- PEN team B: England 1-1 Japan, Japan qualifies.

The scenario marks those matches finished, stores the local `api_status` label, sets `winner_team_id`, and calls the existing `MatchPredictionSettlementService`. It is safe to repeat after `demo:reset-staging`; reruns recalculate the same prediction rows and should not duplicate points.

Scenarios should be named and documented so QA knows what state each scenario creates.

## Recommended QA Flow

Use this flow for local or Railway staging QA:

1. Prepare staging data:

```bash
php artisan demo:reset-staging --force
```

2. Run manual or Playwright pre-results QA.

3. Simulate API-like result arrival:

```bash
php artisan demo:simulate-results --scenario=group-day-1 --force
```

4. Run manual or Playwright post-results QA.

This validates both the pre-results prediction experience and the post-results history/ranking experience.

For knockout-specific QA, use this local/staging-only flow:

1. Reset deterministic demo data:

```bash
php artisan demo:reset-staging --force
```

2. Before simulation, manually verify:

- `E20 knockout QA open UX` allows non-draw qualified-team inference and draw + qualified-team selection.
- `E20 knockout QA closed read-only` shows the saved score and qualified team but cannot be edited.

3. Simulate knockout results:

```bash
php artisan demo:simulate-results --scenario=knockout-qa --force
```

4. Verify post-simulation (expected values are asserted by `tests/Feature/DemoKnockoutQaCommandTest.php`):

- `/my-predictions` shows scored FT, AET, PEN team A, and PEN team B examples.
- Each finished QA match settles `winner_team_id` (FT/AET from the score, PEN from the penalty winner) and scores predictions on the 8/5/5/3/2/0 matrix. `E20 knockout QA PEN team A` exercises all six tiers (e.g. `mariano_demo` = 8, `juan_demo` = 5, `lucia_demo` = 3, `diego_demo` = 2, `sofia_demo` = 0); `E20 knockout QA PEN team B` confirms the inverse qualified-team case.
- `/leaderboard` and `Liga Demo Palermo` include the updated totals (`mariano_demo` totals 47 points across the seeded QA predictions).
- `php artisan prode:check-finished-matches` exits clean.
- Re-running the same simulation is idempotent: totals, awarded points, and prediction counts do not change.

## Staging Reset And Seed Flow

Current command:

```bash
php artisan demo:reset-staging --force
```

Alternative explicit flow:

```bash
php artisan migrate:fresh --seed --force
php artisan db:seed --class=StagingDemoSeeder --force
```

Any destructive reset must be blocked outside local, testing, and staging environments. Future reset commands should confirm that `APP_ENV` and `APP_MODE` are safe before running destructive operations.

The current reset command prepares Phase A, the pre-results QA state. Use `demo:simulate-results` when QA needs to move from pre-results checks to post-results checks.

## Playwright QA Smoke Suite

Playwright runs from the local machine against either a local Laravel server or Railway staging. It uses `PLAYWRIGHT_BASE_URL` when provided and falls back to:

```text
http://127.0.0.1:8000
```

Install Playwright browsers when needed:

```bash
npx playwright install chromium
```

Run against local, after serving the app locally:

```bash
npm run test:e2e
```

Run against Railway staging from PowerShell:

```powershell
$env:PLAYWRIGHT_BASE_URL="https://YOUR-RAILWAY-URL"
npm run test:e2e
```

Open the latest HTML report:

```bash
npm run test:e2e:report
```

Current smoke coverage:

- auth smoke
- prediction pre-results flow
- leagues hub and private league ranking smoke
- history pending/scored state smoke
- admin dashboard and admin matches smoke

Google login QA:

- Google OAuth login requires environment credentials: `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, and `GOOGLE_REDIRECT_URI`.
- The Google login button is hidden when those credentials are missing.
- Playwright smoke tests do not perform real Google OAuth login or call Google.
- When credentials are configured in local or Railway staging, manually verify the `Continuar con Google` button appears on login/register and completes the Google OAuth flow.

Email verification by code QA:

- Email/password registration sends a 6-digit verification code and redirects the user to `/email/verify-code`.
- New registered users must verify their email before accessing dashboard, predictions, leagues, calendar, history, profile, or admin areas.
- Verification codes are stored hashed, expire after 15 minutes, and are invalidated when a new code is resent.
- Successful registration and resend actions should show a clear success toast confirming that the code was sent.
- Verification emails use the Brevo Transactional Email HTTP API, not SMTP. Railway staging hit socket timeouts with Brevo SMTP (`smtp-relay.brevo.com`) on ports 587 and 2525.
- Verification emails are intentionally lightweight HTML with inline styles, no images, and a plain-text fallback.
- Local and automated tests use `Http::fake()` for Brevo and never call the real Brevo API.
- Required Railway variables for verification email delivery:
  - `BREVO_API_KEY`
  - `BREVO_TRANSACTIONAL_FROM_EMAIL=no-reply@miprode.es`
  - `BREVO_TRANSACTIONAL_FROM_NAME="Mi Prode"`
  - `BREVO_API_TIMEOUT=10`
- `MAIL_MAILER` can remain `log` unless other Laravel emails need SMTP. Verification-code delivery does not use Laravel Mail/SMTP.
- Staging demo users remain verified after `php artisan demo:reset-staging --force`, so Playwright smoke can log in with the documented demo accounts without completing email verification.

Registration and verification abuse protection:

- Registration and verification-code email sending use cache-backed limits to protect Brevo free/low-volume quota from repeated bot registrations or resend attempts.
- Limits are configurable per environment. Conservative Railway staging values are recommended:
  - `REGISTRATION_DAILY_LIMIT=50`
  - `REGISTRATION_IP_HOURLY_LIMIT=5`
  - `VERIFICATION_EMAIL_DAILY_LIMIT=80`
  - `VERIFICATION_RESEND_USER_HOURLY_LIMIT=5`
  - `VERIFICATION_RESEND_USER_DAILY_LIMIT=10`
  - `VERIFICATION_RESEND_COOLDOWN_SECONDS=60`
  - `ABUSE_ALERT_EMAIL=` optionally set to the admin email that should receive security limit alerts
  - `ABUSE_ALERT_COOLDOWN_MINUTES=360`
- Production can raise or lower these limits after real usage is known. Keep `VERIFICATION_EMAIL_DAILY_LIMIT` below the effective Brevo quota so a spike cannot consume all transactional email capacity.
- Alert emails are sent through the Brevo HTTP API and are rate-limited per alert type. If alert delivery fails, the app logs the failure and does not retry recursively.

Browser and reporting targets:

- Chromium first
- screenshots on failure
- traces retained on failure
- HTML report

Playwright does not require direct database access. It uses stable staging demo accounts and the deterministic staging demo dataset.

Recommended Playwright QA flow:

1. Prepare staging data:

```bash
php artisan demo:reset-staging --force
```

2. Run pre-results E2E smoke:

```bash
npm run test:e2e
```

3. Simulate results:

```bash
php artisan demo:simulate-results --scenario=group-day-1 --force
```

4. Run post-results E2E smoke:

```bash
npm run test:e2e
```

5. Review the HTML report:

```bash
npm run test:e2e:report
```

Future features that change user flows, admin flows, navigation, prediction states, result lifecycle, league behavior, or scoring visibility should update the related Playwright tests.

## QA Maintenance Rule

Whenever a feature changes a user-visible flow, admin flow, scoring behavior, league behavior, navigation, or result lifecycle, the related QA documentation and/or Playwright tests must be reviewed and updated in the same ticket or in a follow-up QA ticket.

Examples:

- adding a new prediction state requires updating QA scenarios
- changing league navigation requires updating Playwright league tests
- changing scoring rules requires updating post-results QA
- changing admin result flow requires updating admin QA

## Suggested Future Tickets

- E14-T02A - Document staging QA strategy
- E14-T02B - Add staging demo seed and safe reset command
- E14-T02C - Add demo result simulation command
- E14-T02E - Run staging QA and produce report

## Manual Staging Smoke Checklist

- login and register pages load
- predictions page loads
- saving a prediction works
- history page loads
- leagues hub loads
- private league create/search/join flow works
- admin dashboard loads
- admin result save works
- ranking updates after simulated result

## Out Of Scope For E14-T02D

- No migrations.
- No models.
- No controllers.
- No views.
- No external packages beyond Playwright.
- No Railway config changes.
- No real API integration.
- No exhaustive browser matrix.
- No load testing.
