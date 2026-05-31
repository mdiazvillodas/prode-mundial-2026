# Prode Mundial 2026 - Source of Truth

Last updated: 2026-05-31

## Product Summary

Prode Mundial 2026 is a mobile-first web platform where users predict FIFA World Cup 2026 match results and compete with friends through points-based leaderboards.

The product is for entertainment only. It does not include real money, monetary bets, cash prizes, odds, wagers, or gambling mechanics. User-facing language must use terms such as prediction, points, league, ranking, and leaderboard.

## v1 Goals

- Allow users to register, log in, and manage their own predictions.
- Show the World Cup 2026 tournament structure, teams, phases, matches, and results.
- Let users submit and edit score predictions until 5 minutes before match start.
- Award points automatically based on prediction accuracy.
- Provide a general leaderboard for all users.
- Support private leagues with owner approval and member management.
- Support knockout placeholders and qualified-team predictions once teams are known.
- Provide an admin area for tournament data, real results, and ranking recalculations.
- Keep the application simple enough for one developer to maintain.

## Out of Scope for v1

- Real-money betting or gambling mechanics.
- Cash prizes, paid entry, odds, wagering, or casino-style language.
- Microservices, CQRS, Event Sourcing, Kubernetes, Redis, RabbitMQ, or unnecessary infrastructure.
- React, Vue, Inertia, SPA-first architecture, or mobile native apps.
- Complex social features beyond private leagues, search, join requests, approvals, and removals.
- Automatic ingestion from third-party sports data providers unless added by a future decision.

## Technical Stack

- Laravel 12
- PHP 8.2+
- Blade Templates
- Tailwind CSS
- Vite
- Vanilla JavaScript
- MySQL
- Laravel Breeze
- Pest / Laravel Test Suite
- Railway
- Caddy / FrankenPHP
- GitHub

## Architecture Principles

- Prefer Laravel conventions.
- Use MVC.
- Use Eloquent ORM.
- Use migrations and foreign keys.
- Keep logic readable and maintainable.
- Add abstractions only when they remove real complexity.
- Keep changes small and reviewable.
- Build mobile first and make layouts responsive by default.

## Core Domain

### Users

- Users can register and log in.
- Users automatically participate in the general league.
- Admin users can manage tournament and scoring data.

### Tournament Model

- The app stores teams, tournament phases, matches, and real results.
- Matches have a start date and time.
- Knockout matches may initially exist as placeholders without defined teams.
- Placeholder matches cannot receive user predictions until both teams are known.
- Because PHP 8 uses `match` as a reserved language construct, the Eloquent model for the `matches` table is `App\Models\TournamentMatch` instead of `App\Models\Match`.
- Future code, controllers, relationships, and documentation must refer to the Eloquent model as `TournamentMatch`.

### Predictions

- The primary prediction experience is an inline predictions screen grouped by date/day.
- Users can view matches and enter score predictions directly in the match list.
- Predictable matches show inline score inputs.
- Existing predictions are pre-filled.
- When a user changes any input, a floating save button appears.
- Users can save multiple changed predictions at once.
- Closed, locked, finished, and placeholder matches are visible but cannot be edited.
- The existing one-match-at-a-time prediction page may remain as a fallback or internal route, but it is not the primary prediction UX.
- Users can edit predictions until 5 minutes before match start.
- Once the edit deadline passes, predictions are locked.
- For knockout matches, users predict both score and qualified team.

### Scoring

Group stage and non-knockout scoring:

- Exact result: 6 points.
- Correct winner or correct draw without exact score: 3 points.
- Incorrect prediction: 0 points.

Knockout scoring:

- Exact score plus correct qualified team: 6 points.
- Correct qualified team without exact score: 3 points.
- Incorrect prediction: 0 points.

### Leaderboards

- Users are ranked by accumulated points.
- The general league includes all users automatically.
- Private leagues have their own leaderboards.
- Ranking recalculations can be triggered by admin users.

### Private Leagues

- A user can create at most 1 private league.
- A user can belong to at most 3 private leagues.
- Private leagues may have duplicate names.
- Every private league must have a unique visible ID or code.
- Users can search private leagues by name or ID.
- Joining a private league requires approval from the league owner.
- League owners can remove members.
- Member removal actions must be logged.

### Admin

Admin users can manage:

- Teams.
- Tournament phases.
- Matches.
- Placeholder knockout matches.
- Real results.
- Ranking recalculations.

The admin dashboard must show:

- Current environment.
- Whether the app is in test mode or live mode.

## Environments

- Test mode is used for development, validation, and non-production data.
- Live mode is used for the production application.
- Admin screens must make the current mode visible.

## Project Management

- No Jira for now.
- Product and technical scope live in this file.
- Work is tracked in `docs/backlog.md` and `docs/sprints.md`.
- One ticket should map to one clean commit whenever possible.
