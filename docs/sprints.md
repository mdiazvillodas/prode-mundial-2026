# Prode Mundial 2026 - Sprint Plan

Last updated: 2026-05-30

## Sprint 0: documentation and technical base

Goal: establish the source of truth and initialize the future Laravel foundation.

Tickets:

- E0-T01 - Document product scope and working rules.
- E0-T02 - Initialize Laravel technical base.

Expected outcome:

- Documentation is complete enough to guide implementation.
- The application base is ready for domain work.

## Sprint 1: tournament model

Goal: define the core tournament structure.

Tickets:

- E1-T01 - Create tournament data model.
- E1-T02 - Seed initial tournament structure.

Expected outcome:

- Teams, phases, matches, and placeholders can be represented.
- Development data exists for the first user-facing pages.

## Sprint 2: auth, users, matches and predictions

Goal: let users access the app, view matches, and submit regular score predictions.

Tickets:

- E2-T01 - Configure user authentication.
- E2-T02 - Add admin role flag.
- E3-T01 - Show match calendar.
- E4-T01 - Submit and edit score predictions.

Expected outcome:

- Users can register, log in, view matches, and manage predictions before lock time.
- Admin access can be protected.

## Sprint 3: scoring and leaderboard

Goal: calculate points and display the general ranking.

Tickets:

- E5-T01 - Calculate prediction points.
- E6-T01 - Show general leaderboard.

Expected outcome:

- Prediction points are calculated from real results.
- All users appear in the general leaderboard.

## Sprint 4: private leagues

Goal: allow users to create and view private league rankings.

Tickets:

- E7-T01 - Create private leagues.
- E7-T02 - Show private league leaderboard.

Expected outcome:

- Users can create at most one private league.
- Private league members can view league-specific rankings.

## Sprint 5: invitations, member management and admin

Goal: complete private league membership workflows and admin tournament operations.

Tickets:

- E8-T01 - Search and request to join private leagues.
- E8-T02 - Remove private league members with audit log.
- E9-T01 - Build admin tournament management.
- E9-T02 - Add ranking recalculation admin action.

Expected outcome:

- Users can request to join leagues and owners can approve or reject them.
- Owners can remove members with audit logging.
- Admin users can manage tournament data and recalculate rankings.

## Sprint 6: knockout stage

Goal: support knockout match placeholders and qualified-team predictions.

Tickets:

- E10-T01 - Support knockout placeholders.
- E10-T02 - Add knockout qualified-team predictions.

Expected outcome:

- Knockout placeholders are visible but locked for predictions.
- Ready knockout matches support score plus qualified-team prediction.

## Sprint 7: UX/UI

Goal: polish the mobile-first experience across the main flows.

Tickets:

- E11-T01 - Polish mobile-first user experience.

Expected outcome:

- Primary flows are comfortable on mobile and readable on desktop.
- User-facing copy stays entertainment-focused and avoids gambling language.

## Sprint 8: tests and deployment hardening

Goal: harden the v1 release and prepare Railway deployment.

Tickets:

- E12-T01 - Add v1 feature coverage.
- E12-T02 - Harden validation and authorization.
- E13-T01 - Prepare Railway deployment.

Expected outcome:

- Critical rules are covered by tests.
- Authorization and validation are reviewed.
- Deployment configuration is ready for Railway.
