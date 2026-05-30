# Codex Rules

Last updated: 2026-05-30

These rules apply to future Codex work in this repository.

## Before Starting

- Always read `docs/source.md` before implementing.
- Always check `docs/backlog.md` for the current ticket.
- Work on only one ticket at a time.
- Confirm or document ambiguous requirements before implementing.
- If a requirement is ambiguous, update `docs/decisions-log.md` or ask before implementing.

## Scope Control

- Do not implement out-of-scope features.
- Prefer small, reviewable changes.
- One ticket should equal one clean commit whenever possible.
- After each ticket, suggest the commit message from the ticket.

## Technical Rules

- Use Laravel conventions.
- Keep the app simple and maintainable by one developer.
- Use MVC.
- Use Eloquent ORM.
- Use migrations and foreign keys.
- Do not introduce unnecessary dependencies.
- Do not create abstractions unless clearly needed.
- Keep Blade + Tailwind CSS + vanilla JavaScript.
- Do not use React, Vue, or Inertia.
- Do not add microservices, CQRS, Event Sourcing, Kubernetes, Redis, RabbitMQ, or unnecessary infrastructure.

## Product Language

- The product is entertainment only.
- Do not use gambling or betting language in user-facing copy.
- Prefer words such as prediction, points, league, ranking, and leaderboard.
- Do not add real-money betting, monetary prizes, odds, wagers, or gambling mechanics.

## Documentation

- Keep `docs/source.md` as the source of truth.
- Keep `docs/backlog.md` updated when ticket scope changes.
- Keep `docs/sprints.md` updated when ticket sequencing changes.
- Record confirmed product or technical decisions in `docs/decisions-log.md`.
