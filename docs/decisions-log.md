# Prode Mundial 2026 - Decisions Log

## 2026-05-30

- Local path is `C:/dev/prode/prode-mundial-2026`.
- No Jira for now.
- Project management will happen through `docs/backlog.md` and `docs/sprints.md`.
- One ticket equals one clean commit whenever possible.
- Product is entertainment only, with no real-money betting.
- User-facing language must avoid gambling and betting terms.
- Stack is Laravel 12, PHP 8.2+, Blade Templates, Tailwind CSS, Vite, vanilla JavaScript, MySQL, Laravel Breeze, Pest / Laravel Test Suite, Railway, Caddy / FrankenPHP, and GitHub.
- Architecture should stay simple and follow Laravel conventions.
- The app should use MVC, Eloquent ORM, migrations, and foreign keys.
- Group stage and standard match scoring is: exact result 6 points, correct winner or correct draw without exact score 3 points, incorrect prediction 0 points.
- Knockout scoring is: exact score plus correct qualified team 6 points, correct qualified team without exact score 3 points, incorrect prediction 0 points.
- Users can edit predictions until 5 minutes before match start.
- Knockout matches may exist as placeholders without defined teams.
- Users cannot predict placeholder matches until both teams are known.
- Users automatically participate in the general league.
- A user can create at most 1 private league.
- A user can belong to at most 3 private leagues.
- Private leagues may have duplicate names.
- Every private league must have a unique visible ID or code.
- Users can search leagues by name or ID.
- Joining a private league requires approval from the league owner.
- League owners can remove members, and each removal must be logged.
- Admin users can manage teams, matches, phases, real results, and ranking recalculations.
- Admin dashboard must show current environment and whether the app is in test mode or live mode.
- Because PHP 8 uses `match` as a reserved language construct, the Eloquent model for the `matches` table will be named `App\Models\TournamentMatch` instead of `App\Models\Match`.
- The database table remains `matches`.
- Future code, controllers, relationships, and documentation must refer to the Eloquent model as `TournamentMatch`.

## 2026-05-31

- The primary prediction UX will not be a one-match-at-a-time flow.
- The main user experience for submitting predictions will be an inline predictions screen grouped by date/day.
- Users will open a predictions page where matches are grouped by date/day.
- Each predictable match will show score inputs inline.
- Existing predictions will be pre-filled.
- When the user changes any input, a floating save button will appear.
- Users can save multiple changed predictions at once.
- Closed, locked, finished, and placeholder matches will be shown but cannot be edited.
- The existing single-match prediction page may remain as a fallback or internal route for now, but the primary UX should be the inline daily prediction screen.
- The current `PredictionController`, `Prediction` model, validation rules, `updateOrCreate` logic, and `TournamentMatch::isPredictable()` should be reused where possible.

## 2026-06-02

- `Partidos` is redundant as a primary navigation item because `Predicciones` already shows matches with the main user action.
- The `/matches` route may remain for compatibility or internal access, but it should not be treated as a primary user destination.
- `Calendario` should not be another generic match list.
- The calendar should become a team-focused schedule screen where the user selects a team and sees that team's known matches in the user's local timezone.
- Team schedule cards should show upcoming matches, match date and local time, group or stage, opponent, status, and result when finished.
- For v1, the app must not infer hypothetical knockout paths. A team should appear in future knockout matches only after that team has been assigned to the `TournamentMatch` by admin or future API integration.
