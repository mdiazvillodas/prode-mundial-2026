# Team Identity Strategy

Last updated: 2026-06-05

## Overview

Team identity in the Prode application is managed through two distinct asset references: flags and team logos. Both are stored as paths/URLs to external resources rather than binary data, keeping the database lean and supporting flexible asset management.

## Flag Management

### Purpose

Flags represent the national identity of each team and are used throughout the UI to provide visual recognition of teams at a glance.

### Implementation

- **Field**: `teams.flag_path` (nullable string)
- **Format**: Local asset path, e.g., `flags/arg.svg` or `flags/bra.svg`
- **Storage**: SVG files stored in `public/flags/`
- **Type**: Scalable (SVG preferred) or raster
- **Source**: Curated local collection, not downloaded from API

### Local Asset Convention

Team flags live under:

```text
public/flags/
```

Use lowercase SVG filenames based on the football team code or a documented country-code edge case:

```text
flags/arg.svg
flags/bra.svg
flags/eng.svg
flags/wal.svg
flags/usa.svg
flags/mex.svg
```

The committed SVGs are local static assets. Do not hotlink external flag URLs, do not use FIFA or tournament branding, and do not store image binaries in the database.

### No Binary Storage

Flags are **never** stored as binary data in the database. This keeps queries fast and storage efficient. Instead:

- Each team record stores only the relative path to the flag asset
- The asset file is referenced by the web server
- Changes to flag assets do not require database migration

### Creating New Flags

To add a team flag:

1. Add the flag file to `public/flags/` (e.g., `public/flags/arg.svg`)
2. Update `config/team-flags.php` with the team code, `country_code`, and relative flag path
3. Run the mapping command to update team records
4. Use the path in UI templates through the model helper or `asset($team->flag_path)`.

```bash
php artisan teams:apply-flag-mapping --dry-run
php artisan teams:apply-flag-mapping --force
```

Use `--force-update` only when intentionally replacing existing manual `country_code` or `flag_path` values:

```bash
php artisan teams:apply-flag-mapping --force --force-update
```

### Code Mapping

`config/team-flags.php` maps known football team codes to local identity fields:

```php
'ARG' => ['country_code' => 'ARG', 'flag_path' => 'flags/arg.svg'],
```

The mapping command:

- Sets `country_code` and `flag_path` for known teams.
- Preserves existing values by default.
- Reports missing mappings and missing assets without failing the whole run.
- Does not modify `logo_url`.

For flag mapping, `country_code` is a local team flag code. It is usually an ISO 3166-1 alpha-3 country code, but it is not an ISO-2 field and it deliberately supports football/team identity codes when those are clearer for UI display and local assets.

The 2026 API-Football team set has local flag coverage. After syncing 2026 teams, run:

```bash
php artisan teams:apply-flag-mapping --force
```

All mapped World Cup 2026 teams should receive a non-null `flag_path` when their `short_name` matches a configured team code and the asset exists.

Documented edge cases:

- API-Football may use `COS` for Costa Rica; map both `COS` and `CRC` to `flags/crc.svg`.
- England uses `ENG` and `flags/eng.svg`, not a generic Great Britain flag.
- Wales uses `WAL` and `flags/wal.svg`, not a generic Great Britain flag.
- Scotland uses `SCO` and `flags/sco.svg`, not a generic Great Britain flag.
- Curacao uses `CUR`; Cape Verde uses `CPV`; Congo DR uses `CGO`; Ivory Coast uses `CIV`; South Africa uses `RSA`.
- South Korea uses `KOR`.
- Saudi Arabia uses `KSA`.

## Logo Management

### Purpose

Team logos are brand/identity assets provided by the API-Football service and may change across seasons or sources.

### Implementation

- **Field**: `teams.logo_url` (nullable string)
- **Format**: External HTTP/HTTPS URL
- **Type**: Typically PNG with transparency
- **Source**: API-Football `/teams` endpoint during sync (E16-T02)
- **No Downloads**: URLs are stored, not the actual image files

### No Image Binary Storage

Logos are **never** downloaded and stored in the database. Instead:

- The app stores only the URL reference from the API
- The browser fetches the image directly from the API-provided URL when rendering
- This keeps database small and ensures logos stay current

### Cache Strategy (Future)

If logo availability becomes unreliable or if offline support is required, consider:

1. Caching logos as static assets on the local web server (not in database)
2. Using HTTP caching headers to reduce repeated downloads
3. Implementing a background job to periodically refresh logo CDN copies

For v1, direct URL reference is sufficient.

## Team Country Information

### Fields

- `teams.country_code` (nullable string, up to 3 chars): ISO 3166-1 alpha-3 code (e.g., 'ARG', 'BRA')
- `teams.country` (nullable string): Country name from API (e.g., 'Argentina', 'Brazil')

### Source

`teams.country` is populated during team sync (E16-T02) from API-Football response:

```
API response: team.country     -> database: teams.country
API response: team.code        -> database: teams.short_name
```

`teams.country_code` remains a local visual identity field. It stores the configured team flag code, usually ISO alpha-3 but not ISO-2. API-Football team sync may populate it from `config/team-flags.php` only when the field is currently null. It does not overwrite manually set local identity values. The API-Football `venue.country` field describes the team's home stadium location, not the team's national identity, and is not used.

## User Interface

### Displaying Flags

Use the reusable Blade component for team flags in match cards and team-facing UI:

```blade
<x-team-flag :team="$team" />
<x-team-flag :team="$team" size="lg" />
```

The component reads only local team identity data:

- If `team.flag_path` is present, it renders `<img src="{{ asset($team->flag_path) }}">`.
- Image alt text uses `Bandera de {equipo}`.
- If `flag_path` is missing, it falls back to `short_name`, then `country_code`, then initials/name.
- If the team is null or the match is a placeholder, it shows a neutral `TBD` badge with `Equipo por definir` as the accessible label.
- `teams.short_name` is the local display/API team code field. There is no `teams.code` column.
- `logo_url` is not used as the primary UI flag and must not replace `flag_path` in match cards.

### Displaying Logos

```blade
<img src="{{ $team->logo_url }}" alt="{{ $team->name }}" class="team-logo" />
```

### Fallback Handling

If an asset is missing or unavailable:

- For flags: Use a placeholder or default color
- For logos: Use the team name or flag as fallback

## Sync Workflow

1. **Team sync** (E16-T02): Fetch teams from API-Football
   - Populate `api_provider = 'api-football'`
   - Populate `api_team_id` from `team.id`
   - Populate `short_name` from `team.code`
   - Populate `logo_url` from `team.logo`
   - Populate `country` from `team.country`
   - Record `last_synced_at`
   - Populate `country_code` and `flag_path` from local mapping only when null
   - Preserve existing manual `country_code` and `flag_path`

2. **Flag mapping command** (E15-T03): Apply local mappings to existing teams
   - Run `php artisan teams:apply-flag-mapping --dry-run`
   - Run `php artisan teams:apply-flag-mapping --force`
   - Use `--force-update` only for deliberate manual replacement

3. **UI rendering**: Read from database
   - Display `flag_path` for national flag (local asset)
   - Use `resources/views/components/team-flag.blade.php` for compact flag rendering and clean fallbacks
   - Display `logo_url` only when a feature specifically needs the API-provided team logo, not as the primary flag
   - Display `name` as team name
   - Display `country` for country information

## Constraints

- Flags must be created/maintained locally; do not depend on API
- API team sync must not overwrite non-null `flag_path` or `country_code`
- Logos must never be stored as binary data
- URLs and paths must be valid and accessible from the browser
- Do not store credentials or authentication tokens in logo/flag URLs
- Asset file sizes must be reasonable (SVG flags < 10KB, logos < 50KB)
