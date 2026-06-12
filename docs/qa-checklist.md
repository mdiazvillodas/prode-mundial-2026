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
- Choose a valid predefined avatar from the prompt and confirm the prompt does not appear again.
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
- Cross-check a known UTC kickoff across dashboard, `/predictions`, and `/calendar`: for example `2026-06-11 14:00 UTC` must show `16:00` in Europe/Madrid, with prediction edit deadline `15:55` wherever the deadline is visible.
- Repeat the `/calendar` check both with `?tz=Europe/Madrid` and with no `tz` query parameter; the first server-rendered calendar view should still show `16:00`, not UTC `14:00` or double-shifted `18:00`.

## B2. Dashboard

- Open `/dashboard` as a verified user with staging demo data.
- Confirm the page does not show the old large `Hola` hero or generic metric cards.
- Confirm `Te falta pronosticar` appears only when the user has missing predictions and each row links to the correct `/predictions` date/timezone.
- On desktop/tablet-wide width, confirm the top dashboard uses the EPIC 18 grid: `Te falta pronosticar` occupies the main 8/12 column and `Hoy en el Mundial` occupies the compact 4/12 sidebar.
- On mobile width, confirm dashboard modules stack in this order when present: `Te falta pronosticar`, `Hoy en el Mundial`, `Tus amigos ya se movieron`, then the remaining modules.
- Confirm `Te falta pronosticar` remains the primary daily action when missing predictions exist.
- Confirm `Hoy en el Mundial` shows the relevant local match day and can include scheduled/upcoming, live-ish, and finished matches.
- Confirm scheduled rows show kickoff time, while live-ish and finished rows show score/status where useful.
- Confirm live-ish rows show sync age when available.
- Confirm compact prediction-state indicators render in `Hoy en el Mundial`: gray dot for no prediction, green dot for trend/correct direction, red dot for incorrect, and violet star for exact.
- Confirm the compact indicators have accessible labels/tooltips and do not repeat long visible text such as `Sin pronóstico` in every row.
- Confirm `Tus amigos ya se movieron` appears in the sidebar only when the user has active private leagues and shared friend activity.
- Confirm `Tus amigos ya se movieron` shows friend completion counts without revealing prediction values.
- As a user with no active private leagues, confirm the full-width `Jugá con tus amigos` card appears below the top dashboard row.
- Confirm the onboarding card explains the flow to create a league, copy/share the invite link, and compete in a ranking.
- Confirm the onboarding CTAs render and point to existing flows: `Crear mi liga` to `/private-leagues/create` and `Buscar liga` to `/private-leagues/search`.
- As a user with at least one active private league, confirm the `Jugá con tus amigos` card is hidden.
- Confirm the internal dashboard header no longer repeats the Prode logo; the global navigation logo remains visible.
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

- As a user with 0 owned private leagues, confirm they can create a private league.
- As a user with 1 owned private league, confirm they cannot create a second private league.
- Search for a league by name and visible code.
- Request membership from a non-owner account.
- Confirm duplicate requests are blocked.
- Log in as owner and accept a request.
- Log in as owner and reject a different request.
- Confirm owner cannot remove themselves.
- Confirm a user can join/request up to 5 private leagues.
- Confirm a user cannot exceed 5 active private league memberships.
- Confirm active membership and owned league limits are respected independently: being a member of other leagues does not block creating the one owned league.
- As a user who participates in at least one private league but owns none, open `/leagues` and confirm existing private league tabs/rankings are visible.
- Confirm the same member-only user sees `+ Crear mi liga` near the league tabs on `/leagues`.
- Create a new private league as that member-only user and confirm creation succeeds.
- Return to `/leagues` and confirm the newly created league appears.
- As a user who already owns a private league, confirm `/leagues` does not show `+ Crear mi liga`.
- As an owner with pending requests in their owned league, confirm the header badge and modal still show pending requests and the approve/reject forms target the correct request.
- Confirm removed members can no longer view the private league detail page.

## E. Leaderboards

- Open the general leaderboard.
- Confirm users, points, exact counts, tendency/correct-outcome counts, and ranking positions render.
- Confirm compact recent-form indicators render when finished/scored matches exist.
- Confirm exact, trend, incorrect, and no-prediction indicators are visually distinct.
- Confirm recent-form indicators do not show prediction score values.
- Open a private league leaderboard when available.
- Confirm removed or non-member users are excluded from private league ranking.
- Confirm ranking order, points, exact counts, tendency counts, and scored prediction counts are unchanged by recent-form indicators.
- After simulated results, confirm rankings update consistently.

## E2. Critical Scoring And Ranking QA

- Exact result: create or identify a finished group-stage match, submit a prediction with the exact final score, settle the result, and confirm the prediction receives 6 points.
- Correct trend: submit a non-exact prediction with the correct winning team or correct draw trend, settle the result, and confirm the prediction receives 3 points.
- Wrong prediction: submit a prediction with the wrong winner/draw trend, settle the result, and confirm the prediction receives 0 points.
- Knockout scoring decision: confirm the documented knockout matrix is the target behavior before implementation: exact score plus correct qualified team = 8, exact score plus wrong qualified team = 5, correct trend plus correct qualified team without exact score = 5, qualified team only = 3, match trend only = 2, fully incorrect = 0.
- Knockout score meaning: confirm the predicted score represents the final played result before penalties, with no distinction between 90-minute and 120-minute results.
- Knockout extra time: create or identify a knockout match that is 1-1 after 90 minutes and finishes 2-1 after extra time; a 2-1 prediction should be treated as exact once the expanded scoring matrix is implemented.
- Knockout penalties: create or identify a knockout match tied after extra time and decided on penalties; confirm penalties only decide the qualified team and do not change the predicted/final played score.
- Knockout non-draw prediction UX: submit a non-draw knockout prediction and confirm the qualified team is inferred from the predicted score winner.
- Knockout draw prediction UX: submit a draw knockout prediction and confirm the UI requires selecting the qualified team with clear team/flag blocks.
- Knockout closed visibility: after prediction close, confirm the read-only prediction summary shows score and qualified team clearly.
- Finished-match winner resolution: using fake/local data only, confirm group FT 2-0 sets the home winner, group FT 1-1 keeps `winner_team_id` null, knockout FT/AET 2-1 sets the winner, and knockout PEN tied scores resolve the qualified team from API winner flags.
- Leaderboard ordering: create users with different point totals, exact counts, trend counts, and a final username tie, then confirm `/leaderboard` orders by points, exacts, trends, and the existing final tie-breaker.
- Private league ranking: confirm a private league ranking uses the same point/exact/trend ordering, includes active members with zero scored predictions, and excludes removed members.
- API-Football finished match sync: using a fake/local snapshot only, sync a finished fixture and confirm local teams, API status, round/stage, venue, and finished score fields map as expected without calling the real API.
- Settlement idempotency: run settlement for the same finished match twice and confirm prediction rows are not duplicated and point totals remain unchanged.
- Finished-match consistency: after fake/local settlement, run the read-only consistency checker when available and confirm it reports no finished matches with null scores, missing knockout winners, or submitted/unscored predictions.
- Staging knockout QA: before production knockout usage, repeat login, group prediction, knockout non-draw prediction, knockout draw + qualified-team prediction, closed prediction visibility, FT/AET/PEN settlement, leaderboard update, and consistency checks in staging only.

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
