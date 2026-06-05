# Staging QA Strategy

Last updated: 2026-06-05

This document defines the staging QA subproject for Prode Mundial 2026. It guides demo seed data, safe reset commands, simulated result updates, and future Playwright end-to-end smoke tests.

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
- Demo reset commands fail loudly when `APP_ENV=production` or `APP_MODE=live`.

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

The dataset should be deterministic so QA can rely on stable accounts, stable leagues, stable match states, and predictable expected rankings.

The demo fixture is controlled QA data. It should not be treated as an official final World Cup fixture unless an official source is explicitly documented in a future ticket.

## Demo Users

The staging demo seeder creates stable demo accounts:

- `admin@prode.test`
- `mariano@prode.test`
- `ana@prode.test`
- `juan@prode.test`

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
- demo users listed above
- `Liga Demo Palermo`, owned by `mariano@prode.test`
- active memberships for Mariano and Ana
- a pending join request for Juan
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

A future command should simulate API-like result arrival:

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

## Future Result Simulation Command

Desired future command:

```bash
php artisan demo:simulate-results --scenario=group-day-1
```

The command should behave like the future API integration:

- apply result data to selected matches
- set `winner_team_id`
- mark matches as finished
- call existing settlement/scoring logic
- avoid duplicating points
- be safe for repeated use where possible
- block execution in production/live

Scenarios should be named and documented so QA knows what state each scenario creates.

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

The current reset command prepares Phase A, the pre-results QA state. Simulated API-like result changes are intentionally left for E14-T02C.

## Playwright QA Strategy

Playwright should later run against Railway staging using a configurable `BASE_URL`.

Recommended suites:

- auth smoke
- prediction pre-results flow
- private league flow
- admin result flow
- post-results ranking/history flow
- navigation/mobile smoke

Browser and reporting targets:

- Chromium first
- include at least one mobile viewport
- capture screenshots on failure
- capture traces on failure
- generate an HTML report

Playwright should not require production data. It should use stable staging demo accounts and the deterministic staging demo dataset.

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
- E14-T02D - Add Playwright staging QA smoke suite
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

## Out Of Scope For E14-T02B

- No Playwright installation.
- No test implementation.
- No migrations.
- No models.
- No controllers.
- No views.
- No external packages.
- No Railway config changes.
- No result simulation command. That belongs to E14-T02C.
