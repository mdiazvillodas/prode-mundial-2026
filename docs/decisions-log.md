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
