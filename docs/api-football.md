# API-Football Discovery

This document covers the read-only API-Football discovery command for preparing World Cup 2026 data.

## Environment

Required variables:

```env
API_FOOTBALL_BASE_URL=https://v3.football.api-sports.io
API_FOOTBALL_KEY=
API_FOOTBALL_WORLD_CUP_LEAGUE_ID=1
API_FOOTBALL_WORLD_CUP_SEASON=2026
API_FOOTBALL_ALLOW_PRODUCTION_SYNC=false
```

Do not commit real API keys. Configure secrets in local `.env` or Railway variables only.

`API_FOOTBALL_ALLOW_PRODUCTION_SYNC` must stay `false` by default. Set it to `true` only for the official production initial sync or production cron that is expected to call API-Football in `APP_ENV=production` or `APP_MODE=live`.

## World Cup 2026

API-Football / API-Sports World Cup 2026 discovery uses:

- `league=1`
- `season=2026`

The free plan is limited. Use discovery sparingly and avoid repeated `all` runs unless needed.

Free API-Sports plans may not have access to season `2026`. A successful HTTP `200` can still return a top-level API-Football error such as:

```json
{
  "errors": {
    "plan": "Free plans do not have access to this season, try from 2022 to 2024."
  }
}
```

The discovery command treats non-empty top-level `errors` as a failed endpoint even when the HTTP status is `200`. If `--save` is used, the raw response is still saved as an error snapshot so the response shape can be inspected.

## Command

### Discovery

Run all supported discovery endpoints and save snapshots:

```bash
php artisan api-football:discover-world-cup --endpoint=all --save --force
```

Run only teams:

```bash
php artisan api-football:discover-world-cup --endpoint=teams --save --force
```

Validate the integration shape with an accessible free-plan season such as `2022`:

```bash
php artisan api-football:discover-world-cup --endpoint=teams --season=2022 --save --force
php artisan api-football:discover-world-cup --endpoint=fixtures --season=2022 --save --force
```

These `2022` snapshots are for structure and mapping practice only. They are not app data for the 2026 product.

Override league and season when needed:

```bash
php artisan api-football:discover-world-cup --endpoint=teams --league=1 --season=2022 --save --force
```

Dry run without calling the API:

```bash
php artisan api-football:discover-world-cup --endpoint=all --dry-run
```

Supported endpoints:

- `/teams?league=1&season=2026`
- `/fixtures?league=1&season=2026`
- `/fixtures/rounds?league=1&season=2026`
- `/standings?league=1&season=2026`

`--endpoint=all` makes at most 4 API requests.

### Team Sync

Sync teams from API-Football into the local `teams` table:

```bash
php artisan api-football:sync-teams --force
```

Run a dry run without writing to the database:

```bash
php artisan api-football:sync-teams --dry-run --force
```

Validate the team-sync shape with an accessible free-plan season such as `2022`:

```bash
php artisan api-football:sync-teams --season=2022 --dry-run --force
```

Load teams from a previously saved snapshot without spending API requests:

```bash
php artisan api-football:sync-teams --from-snapshot=api-football/world-cup-2026/teams-latest.json --dry-run --force
```

`api-football:sync-teams` makes at most 1 API request when not using `--from-snapshot`.

The command:

- Uses `x-apisports-key`.
- Fails when `API_FOOTBALL_KEY` is missing, unless `--from-snapshot` is used.
- Detects top-level API-Football `errors`, including HTTP 200 logical errors.
- Creates, updates, links, or skips teams conservatively.
- Does not delete local teams missing from the API response.
- May populate local `country_code` and `flag_path` from `config/team-flags.php` only when those fields are null.
- Preserves existing manual `country_code` and `flag_path`.
- Ignores `venue.*` data from the teams endpoint.
- Does not sync fixtures, results, predictions, rankings, or admin data.

### Fixture Sync

Sync fixtures from API-Football into the local `matches` table after teams have been synced:

```bash
php artisan api-football:sync-fixtures --force
```

Run a dry run without writing to the database:

```bash
php artisan api-football:sync-fixtures --dry-run --force
```

Validate the fixture-sync shape with an accessible free-plan season such as `2022`:

```bash
php artisan api-football:sync-fixtures --season=2022 --dry-run --force
```

Load fixtures from a previously saved snapshot without spending API requests:

```bash
php artisan api-football:sync-fixtures --from-snapshot=api-football/world-cup-2026/fixtures-latest.json --dry-run --force
```

Run order matters:

```bash
php artisan api-football:sync-teams --force
php artisan api-football:sync-fixtures --force
```

`api-football:sync-fixtures` makes at most 1 API request when not using `--from-snapshot`.

The command:

- Uses `x-apisports-key`.
- Fails when `API_FOOTBALL_KEY` is missing, unless `--from-snapshot` is used.
- Detects top-level API-Football `errors`, including HTTP 200 logical errors.
- Requires local teams matched by `api_provider='api-football'` and `api_team_id`.
- Skips fixtures with missing teams and prints `Team not found. Run api-football:sync-teams first.`
- Upserts local `TournamentMatch` rows using `api_provider` and `api_fixture_id`.
- Maps API home team to `team_a_id` and API away team to `team_b_id`.
- Stores `fixture.date`, `fixture.status.short`, `league.round`, venue name/city, and `last_synced_at`.
- Stores scores for finished (`FT`, `AET`, `PEN`) and live API statuses; live partial scores are stored without marking the match finished or settling predictions.
- Resolves `winner_team_id` for finished matches and settles predictions via `MatchPredictionSettlementService` only when both final scores are present (see Winner Resolution below). Settlement is idempotent.
- Does not create teams, sync rankings, sync leagues, change admin flows, or delete local matches.

Group letters are not inferred from `league.round`. The raw API round is stored in `round`; `stage` is mapped only when the round label is clear, and `group` remains unchanged/null.

#### Round-to-stage mapping (E20-T02)

`league.round` is normalized into a local `matches.stage` value by `TournamentMatch::stageFromApiRound()`. This is the single source of truth for stage detection and is used by the fixture sync command. Matching is case-insensitive and trimmed.

| API round label (examples) | Local `stage` | Knockout? |
| --- | --- | --- |
| `Group Stage - 1`, `Group A` | `group` | no |
| `Round of 32`, `1/16-finals` | `round_of_32` | yes |
| `Round of 16`, `1/8-finals` | `round_of_16` | yes |
| `Quarter-finals`, `1/4-finals` | `quarter_final` | yes |
| `Semi-finals`, `1/2-finals` | `semi_final` | yes |
| `3rd Place Final`, `Third Place` | `third_place` | yes |
| `Final` | `final` | yes |

Notes:

- `third_place` is matched **before** the generic `final` check, because API-Football labels third place as `3rd Place Final`, which also contains the word "final".
- `TournamentMatch::isKnockout()` and `TournamentMatch::requiresQualifiedTeamPrediction()` return `true` for all stages in `TournamentMatch::KNOCKOUT_STAGES` (round of 32 through final, including third place). Group stage does not require a qualified-team prediction.
- Unknown or new round labels are handled conservatively: `stage` is left `null` (so the match is treated as non-knockout and does **not** silently require/skip qualified-team logic for the wrong reason), the raw `round` value is preserved, and the labels are surfaced. The sync command prints an `Unmapped API round labels:` warning, writes a `Log::warning`, and records the labels under `metadata.unknown_rounds` in the sync log so admins can review and extend the mapping.

#### Winner resolution (E20-T03)

`winner_team_id` is resolved during fixture sync only for finished statuses (`FT`, `AET`, `PEN`) when both `team_a_score` and `team_b_score` are present. The local scores hold the final **played** result before penalties (API `goals.home` → `team_a_score`, `goals.away` → `team_b_score`). Penalty shootout scores are not stored in the local score columns.

Resolution rules (`ApiFootballSyncFixturesCommand::resolveWinnerTeamId()`):

| Case | Played score | Stage | `winner_team_id` |
| --- | --- | --- | --- |
| FT/AET non-draw | higher side wins | any | higher-scoring team |
| FT/AET draw | tied | group | null |
| PEN (or tied knockout) | tied | knockout | from API winner flags |
| Tied knockout, flags absent | tied | knockout | null (no guess) |

Notes:

- A tied played score only has a decisive winner in knockout matches. The winner is taken from the API-Football `teams.home.winner` / `teams.away.winner` flags (`true` on the advancing side). API home maps to team A, API away maps to team B.
- When a knockout match is tied and the winner flags are absent, `winner_team_id` stays `null` rather than guessing; admins can re-sync once the API exposes the flags.
- Group-stage draws always keep `winner_team_id` null even if winner flags were present.
- Live/in-progress statuses may store a partial score but never set a final winner, never mark the match finished, and never trigger settlement.
- This ticket does not implement the expanded knockout scoring matrix; settlement still uses the current `PredictionScoringService`.
- An existing match keeps its current `stage` on re-sync; the round-to-stage mapping only fills `stage` when it is currently null.

## Snapshots

When `--save` is passed, raw JSON snapshots are stored on the local disk under:

```text
storage/app/private/api-football/world-cup-2026/
```

Snapshot filenames include:

- `teams-YYYYMMDD-HHMMSS.json`
- `fixtures-YYYYMMDD-HHMMSS.json`
- `rounds-YYYYMMDD-HHMMSS.json`
- `standings-YYYYMMDD-HHMMSS.json`
- `teams-latest.json`
- `fixtures-latest.json`
- `rounds-latest.json`
- `standings-latest.json`

Snapshots are ignored from Git and must not be committed.

## Safety

The discovery command does not change app data. It does not modify teams, matches, predictions, scores, rankings, users, or leagues.

API-Football discovery and sync commands are blocked in production/live mode by default. `--force` only skips interactive confirmation; it does not bypass the production/live guard.

Production or live execution requires:

```env
API_FOOTBALL_ALLOW_PRODUCTION_SYNC=true
```

When this flag is enabled, the commands print a production/live warning before continuing. Use it only for the official production cron or an intentional production initial sync, for example:

```bash
php artisan migrate --force
php artisan api-football:sync-teams --season=2026 --force
php artisan teams:apply-flag-mapping --force
php artisan api-football:sync-fixtures --season=2026 --force
```

Never run destructive demo commands such as `php artisan demo:reset-staging --force` or `php artisan demo:simulate-results` in production. This API-Football flag does not weaken demo reset protections.

Real 2026 sync will require an API plan that can access season `2026`, or an alternative import strategy.

## API Response Mapping

This section documents the confirmed API response shapes and how they map into the application database.

### Teams Endpoint

API-Football `/teams?league=1&season=2026` returns:

```
response[]
  team.id           -> database: api_team_id
  team.name         -> database: name
  team.code         -> database: short_name. This value is considered an external/API short code and is **not** stored directly as the app's canonical `country_code` or `flag_path`. `teams.country_code` and `teams.flag_path` are local visual identity helpers populated from local mapping only when null.
  team.country      -> database: country
  team.national     -> database: (unused - boolean flag)
  team.logo         -> database: logo_url (external URL reference only, not the local team flag)
  venue.*           -> database: (unused - venue data is not used for national team identity)
```

**Important**: The `venue` data in the teams endpoint describes the team's home stadium, not the team's country. It is not used for national team identity. The app tracks team flags separately via `flag_path` (local asset) and `logo_url` (external API-Football reference). `logo_url` is not a substitute for the local `flag_path`.

**Teams mapping into database**:

- `api_provider`: 'api-football' (string, marks data source)
- `api_team_id`: team.id (unsigned big integer, API-Football team ID)
- `name`: team.name (string, team name)
- `short_name`: team.code (string, API short code)
- `country`: team.country (string, from API response)
- `logo_url`: team.logo (string, external URL reference)
- `country_code`: local field populated from `config/team-flags.php` only when null, not overwritten by API sync
- `flag_path`: local asset path such as `flags/arg.svg`, populated from `config/team-flags.php` only when null
- `last_synced_at`: (datetime, timestamp of last sync)

A unique constraint prevents duplicate syncs: `unique(['api_provider', 'api_team_id'])`.

### Fixtures Endpoint

API-Football `/fixtures?league=1&season=2026` returns:

```
response[]
  fixture.id           -> database: api_fixture_id
  fixture.date         -> database: starts_at (already in app)
  fixture.timestamp    -> database: (used for conversion, not stored)
  fixture.venue.name   -> database: venue_name
  fixture.venue.city   -> database: venue_city
  fixture.status.short -> database: api_status (e.g., 'NS', '1H', 'FT')
  league.round         -> database: round (e.g., '1/8', 'Semi-finals')
  teams.home.id        -> database: (linked via team sync)
  teams.home.name      -> database: (reference only)
  teams.home.winner    -> database: (used to determine qualified team)
  teams.away.id        -> database: (linked via team sync)
  teams.away.name      -> database: (reference only)
  teams.away.winner    -> database: (used to determine qualified team)
  goals.home           -> database: team_a_score (already in app)
  goals.away           -> database: team_b_score (already in app)
  score.fulltime.*     -> database: (used for score)
  score.extratime.*    -> database: (unused in v1)
  score.penalty.*      -> database: (unused in v1)
```

**Fixtures mapping into database**:

- `api_provider`: 'api-football' (string, marks data source)
- `api_fixture_id`: fixture.id (unsigned big integer, API-Football fixture ID)
- `api_status`: fixture.status.short (string, match status code)
- `round`: league.round (string, tournament round)
- `venue_name`: fixture.venue.name (string)
- `venue_city`: fixture.venue.city (string)
- `last_synced_at`: (datetime, timestamp of last sync)

A unique constraint prevents duplicate syncs: `unique(['api_provider', 'api_fixture_id'])`.

**Note**: `starts_at`, `team_a_score`, and `team_b_score` are already in the app database and are updated during fixture sync when safe. `winner_team_id` is resolved during fixture sync for finished matches and predictions are settled when both final scores are present — see the Winner resolution (E20-T03) section above.

### Data Flow

1. **Discovery phase** (E16-T01, complete): Add database fields to track API mappings. Database is prepared but no sync occurs.
2. **Team sync** (E16-T02, complete): Fetch teams from `/teams` endpoint and populate database fields.
3. **Fixture sync** (E16-T03, complete): Fetch fixtures from `/fixtures` endpoint and populate database fields without settling predictions.
4. **Sync visibility** (E16-T05, complete): Store compact sync logs and expose an admin health screen.
5. **Result settlement** (E16-T04, planned): Use synced data to update scores and settle predictions.

## Finished-Match Consistency Check

A read-only Artisan command reconciles API-Football-driven results with local match and prediction state:

```bash
php artisan prode:check-finished-matches
```

It never modifies data and is safe to run in production/live (it does not call API-Football and prints no user emails or secrets). It reports, among others, when API reports a finished status (`FT`/`AET`/`PEN`) but the local match is not finished, when a finished match is missing a score, when a knockout finished match has no `winner_team_id`, when a finished match has unscored predictions, and when a finished match has predictions but none are scored. It exits `0` when no critical inconsistencies are found and non-zero otherwise. See `docs/qa-checklist.md` for the full issue-code list and interpretation. This command does not auto-repair anything; it only surfaces issues for an operator to act on.

## Sync Logs And Admin Health

API-Football discovery and sync commands write compact operational records to `api_sync_logs`.

Logged fields include:

- provider and sync type, such as `api-football`, `teams`, `fixtures`, `discovery:teams`, and `discovery:fixtures`
- status: `success`, `failed`, or `skipped`
- HTTP status and rate-limit headers when an API response exists
- started/finished timestamps and duration
- item counts for received, created, updated, and skipped records
- a short error message for failed runs
- small metadata such as league, season, source, endpoint path, dry-run state, and missing-team count

The database log must not store API keys, secrets, or full raw response bodies. Raw discovery snapshots remain in private storage only when the discovery command is run with `--save`.

If writing a sync log fails, the command logs a Laravel warning and continues returning the original sync result. Logging failure must not turn a successful sync into a failed sync.

Admins can view the read-only health screen at:

```text
/admin/api-health
```

The page shows:

- latest successful team and fixture syncs
- latest failed sync
- API teams and API fixtures in the local database
- teams missing `flag_path`
- fixtures grouped by `api_status`
- recent sync logs with status, duration, counts, quota, and short error messages
- copyable command hints for manual team and fixture syncs

Health freshness uses:

```env
API_SYNC_HEALTH_WARNING_MINUTES=15
```

The indicator is `OK` when the latest team and fixture syncs succeeded recently, warning when a successful sync is older than the threshold or missing, and error when the latest run failed.

### UI Data Source

**Important**: The UI must read data from the local database, not directly from the API. The app stores API-provided data locally to:

- Ensure data consistency and integrity.
- Support offline operation.
- Provide audit and logging capabilities.
- Enable admin override and manual corrections.

When displaying team logos, use `logo_url` (external reference). When displaying team flags, use `flag_path` (local asset). Do not call the API from the browser.

## Future Sync Work

Planned follow-up tickets:

- E16-T01 - Add API mapping fields. (IMPLEMENTED)
- E16-T02 - Sync teams from API-Football. (IMPLEMENTED)
- E16-T03 - Sync fixtures from API-Football. (IMPLEMENTED)
- E16-T04 - Sync results and settle predictions.
- E16-T05 - API sync logs/admin visibility. (IMPLEMENTED)
