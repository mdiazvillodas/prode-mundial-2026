# Prode QA Checklist

Use this checklist before staging sign-off and again before production cutover for `miprode.es`.

## Recommended Local QA Flow

Run from the project root:

```bash
php artisan migrate:fresh --seed
php artisan demo:reset-staging --force
php artisan teams:apply-flag-mapping --force
npm run build
npm run dev
php artisan serve
npm run test:e2e
```

For Playwright, set `PLAYWRIGHT_BASE_URL` when testing a non-default host:

```bash
PLAYWRIGHT_BASE_URL=https://staging.example.test npm run test:e2e
```

Do not run destructive demo reset commands in production or live mode. Do not use real Brevo, Google, or API-Football calls as part of automated smoke tests.

If `/predictions` has no editable matches during smoke testing, run `php artisan demo:reset-staging --force` to reseed deterministic staging data with a future open match.

The staging demo reset also includes dashboard engagement scenarios: avatar choice states, a multi-member private league, a four-match next engagement day with varied friend prediction completion, live-ish partial-score matches, and finished scored matches for future form/GF/GC checks.

## A. Auth

- Register with email and password.
- Confirm the verification page appears after registration.
- Receive the Brevo verification email in the expected inbox or staging mail sink.
- Submit the verification code and confirm the account becomes verified.
- Log in with email and password.
- Log out and confirm protected pages redirect to login.
- Log in with Google for an existing app user in staging when Google variables are configured.
- Register/log in with Google using an email that does not already exist in the app.
- Confirm Google-created users land on the dashboard and do not see the email-code verification flow.
- Confirm Google-created users have verified email status in admin/database checks.
- Attempt duplicate email registration and confirm a friendly validation error.
- Use forgot password and reset password if mail delivery is configured for the environment.
- For a verified user with no avatar choice, open the dashboard and confirm the avatar prompt appears.
- Choose `Sin avatar` and confirm the prompt does not appear again.
- Choose a predefined avatar from `/profile` and confirm the saved avatar remains selected after reload.
- Submit an invalid avatar key through a crafted request and confirm it is rejected with a friendly validation error.
- Confirm no API keys, stack traces, or technical provider errors are shown to users.

## B. Predictions

- Open `/predictions` as an authenticated user.
- Confirm date chips show only dates that have matches.
- Select different date chips and confirm match cards change.
- On mobile width, reload with a later `?date=YYYY-MM-DD` and confirm the active chip remains visible or centered.
- Confirm match cards show team names, flags, local times, status, and prediction inputs when open.
- Submit predictions for an open match.
- Edit predictions before lock and confirm the updated values persist after reload.
- Confirm locked, closed, finished, and placeholder matches do not allow edits.
- Confirm `Cierra pronto` appears only when less than or equal to 1 hour remains before prediction lock.
- Confirm matches days away stay labeled as open/scheduled, not closing soon.
- In Europe/Madrid, confirm Argentina vs Algeria on `17/06/2026 01:00 UTC` shows as `17/06/2026 03:00` on `/predictions`.
- Confirm the same match shows `Editar hasta 02:55` on `/predictions`.

## B2. Dashboard

- Open `/dashboard` as a verified user with staging demo data.
- Confirm the page does not show the old large `Hola` hero or generic metric cards.
- Confirm `Te falta pronosticar` appears only when the user has missing predictions and each row links to the correct `/predictions` date.
- Confirm `En juego` appears only for live-ish matches and shows score, user prediction when present, provisional state, and sync age when available.
- Confirm `Tus amigos ya se movieron` shows friend completion counts without revealing prediction values.
- Confirm the compact league summary links to `/leagues`.
- Confirm modules with no data are hidden rather than replaced by filler cards.

## C. Calendar

- Open `/calendar`.
- Confirm the team selector is usable.
- Select a team with known fixtures.
- Confirm fixtures are ordered by start time.
- Confirm flags are visible for selected team and opponent.
- Confirm dates and times are readable on mobile and desktop.
- Confirm empty/invalid team states are friendly.
- Compare the same fixture between `/calendar` and `/predictions`; the visible local date and time must match.
- Test a UTC-midnight boundary fixture, for example `2026-06-16 23:30 UTC`, and confirm it appears under `17/06/2026` for Europe/Madrid.
- Test an Argentina viewer timezone when possible and confirm dates/times shift consistently on both pages.

## D. Private Leagues

- Create a private league.
- Search for a league by name and visible code.
- Request membership from a non-owner account.
- Confirm duplicate requests are blocked.
- Log in as owner and accept a request.
- Log in as owner and reject a different request.
- Confirm owner cannot remove themselves.
- Confirm active membership and owned league limits are respected.
- Confirm removed members can no longer view the private league detail page.

## E. Leaderboards

- Open the general leaderboard.
- Confirm users, points, exact counts, tendency/correct-outcome counts, and ranking positions render.
- Open a private league leaderboard when available.
- Confirm removed or non-member users are excluded from private league ranking.
- After simulated results, confirm rankings update consistently.

## F. Admin

- Log in as an admin user.
- Open `/admin` and confirm environment and app mode are visible.
- Open `/admin/matches`.
- View a match result form for a match with teams.
- Update a match result and confirm prediction settlement feedback appears.
- Open `/admin/users`.
- Confirm users show name, username, email, role, email verification status, Google linked status, and created date.
- Create/register a user that remains unverified, then log in as admin and manually verify the user's email from `/admin/users`.
- Confirm the manually verified user can access the app through the existing email verification gate.
- Confirm this is only an email verification override, not an account approval/rejection workflow.
- Open `/admin/api-health`.
- Confirm API sync logs, latest success/failure, team/fixture counts, missing flag count, and fixture status counts render.
- Confirm a normal user receives `403` for admin pages.
- Confirm a guest is redirected to login for admin pages.

## G. API-Football Data Integrity

- Confirm there are 48 API-Football teams in the database for World Cup 2026.
- Confirm there are 72 API-Football fixtures in the database for World Cup 2026.
- Confirm `teams.short_name` is used as the API/display code; do not assume a `teams.code` column.
- Confirm 0 API teams are missing `flag_path`.
- Confirm 0 API fixtures are missing `team_a_id` or `team_b_id`.
- Confirm latest teams sync log is `success`.
- Confirm latest fixtures sync log is `success`.
- Confirm raw API responses are not stored in the database.
- Confirm the future cron/scheduler service is configured separately from the web service.
- Confirm production/live API-Football sync refuses by default when `API_FOOTBALL_ALLOW_PRODUCTION_SYNC=false`.
- Confirm the production cron or initial sync has `API_FOOTBALL_ALLOW_PRODUCTION_SYNC=true` only when intentional.
- Confirm destructive demo reset/simulation commands are never run in production/live.

Useful local integrity checks:

```bash
php artisan tinker
```

```php
App\Models\Team::where('api_provider', 'api-football')->count();
App\Models\TournamentMatch::where('api_provider', 'api-football')->count();
App\Models\Team::where('api_provider', 'api-football')->whereNull('flag_path')->count();
App\Models\TournamentMatch::where('api_provider', 'api-football')->where(fn ($q) => $q->whereNull('team_a_id')->orWhereNull('team_b_id'))->count();
App\Models\ApiSyncLog::where('provider', 'api-football')->latest()->first();
```

## H. Mobile And Responsive

- Login and registration forms fit without horizontal overflow.
- The app navigation is visible and usable.
- `/predictions` cards fit on common mobile widths.
- Date chips scroll horizontally and the active chip remains visible.
- Score inputs are large enough to tap.
- Team names, flags, and scores do not overlap.
- Calendar selector and cards are usable on mobile.
- League tables remain readable or scroll safely.

## I. Abuse Protection

- Resend verification code cooldown blocks immediate repeat requests with friendly messaging.
- User hourly and daily verification resend limits behave as configured.
- IP and global registration limits fail gracefully.
- Honeypot-filled registration does not create a user or send email.
- Missing Brevo configuration does not produce a 500 for users.
- Brevo non-2xx and connection failures redirect safely with user-friendly messaging.

## Automated Smoke Coverage

Expected local smoke coverage:

- Guest can see login, registration, and forgot-password pages.
- Guest is redirected from protected pages.
- Demo user can log in and access dashboard.
- Demo user can access `/predictions`.
- `/predictions` shows date chips and match cards when demo data exists.
- Demo user can change prediction dates when multiple date chips exist.
- Demo user can access `/calendar`.
- Demo user can access `/leaderboard`.
- Admin user can access `/admin/api-health`.
- Normal user cannot access `/admin/api-health`.

Automated smoke tests must not automate real Google login, call Brevo, call API-Football, or depend on external services.
