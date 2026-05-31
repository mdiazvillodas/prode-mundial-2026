# Prode Mundial 2026 - v1 Backlog

Last updated: 2026-05-30

Statuses: `Todo`, `In Progress`, `Done`, `Blocked`

## EPIC 0 - Base tecnica

### Ticket ID
E0-T01

### Title
Document product scope and working rules

### Status
Done

### Sprint
Sprint 0

### Priority
High

### Objective
Create the initial documentation that defines product scope, backlog, sprint plan, decisions, and future Codex rules.

### Scope
- Complete `docs/source.md`.
- Complete `docs/backlog.md`.
- Complete `docs/sprints.md`.
- Complete `docs/decisions-log.md`.
- Create `docs/codex-rules.md`.

### Out of scope
- Laravel application code.
- Dependency installation.
- Package file changes.
- Application initialization.

### Acceptance criteria
- Requested documentation files exist in `docs/`.
- Product scope, rules, stack, and constraints are documented.
- Backlog uses the agreed ticket format.
- Sprint plan covers Sprint 0 through Sprint 8.

### Suggested commit message
docs: define product scope and v1 plan

### Ticket ID
E0-T02

### Title
Initialize Laravel technical base

### Status
Todo

### Sprint
Sprint 0

### Priority
High

### Objective
Create the Laravel 12 application foundation using the agreed stack.

### Scope
- Initialize Laravel 12.
- Configure PHP 8.2+ expectations.
- Add Laravel Breeze with Blade.
- Configure Tailwind CSS, Vite, and vanilla JavaScript.
- Prepare MySQL environment variables.
- Add baseline Pest / Laravel test setup.

### Out of scope
- Domain models.
- Tournament data.
- Prediction logic.
- Deployment.

### Acceptance criteria
- App boots locally.
- Authentication scaffold is ready for future tickets.
- Baseline tests run.
- No React, Vue, or Inertia is introduced.

### Suggested commit message
chore: initialize laravel application base

## EPIC 1 - Modelo del torneo

### Ticket ID
TICKET-003

### Title
Crear modelo Team

### Estado
DONE

### Sprint
Sprint 1

### Prioridad
High

### Objetivo
Representar las selecciones que participan en el Mundial 2026.

### Alcance
- Crear migration teams.
- Crear modelo Team.
- Crear factory.
- Crear TeamSeeder.
- Campos mínimos:
  - id
  - name
  - short_name
  - country_code
  - flag_path
  - created_at
  - updated_at

### Fuera de alcance
- No crear pantallas admin.
- No crear lógica de partidos.
- No crear lógica de torneo.
- No crear predicciones.
- No importar equipos desde una API.
- No customizar UI.
- No modificar autenticación.

### Criterios de aceptación
- El modelo Team existe.
- La tabla teams existe.
- TeamFactory existe.
- migrate:fresh --seed crea equipos de prueba.
- No se implementan funcionalidades fuera de alcance.

### Commit sugerido
Add teams model and migration

### Ticket ID
TICKET-004

### Title
Crear modelo Tournament

### Estado
DONE

### Sprint
Sprint 1

### Prioridad
High

### Objetivo
Representar el Mundial FIFA 2026 como torneo principal de la plataforma.

### Alcance
- Crear migration tournaments.
- Crear modelo Tournament.
- Crear factory.
- Crear TournamentSeeder.
- Asociar el torneo inicial “FIFA World Cup 2026”.
- Campos mínimos sugeridos:
  - id
  - name
  - slug
  - year
  - starts_at
  - ends_at
  - status
  - created_at
  - updated_at

### Fuera de alcance
- No crear partidos todavía.
- No crear fases todavía.
- No crear grupos todavía.
- No crear pantalla admin.
- No implementar predicciones.

### Criterios de aceptación
- El modelo Tournament existe.
- La tabla tournaments existe.
- migrate:fresh --seed crea el torneo “FIFA World Cup 2026”.
- No se implementan funcionalidades fuera de alcance.

### Commit sugerido
Add tournaments model and seed world cup

### Ticket ID
TICKET-005

### Title
Crear modelo Match

### Estado
DONE

### Sprint
Sprint 1

### Prioridad
High

### Objetivo
Representar los partidos del Mundial 2026.

### Alcance
- Crear migration matches.
- Crear modelo Match.
- Relacionar Match con Tournament.
- Relacionar Match con Team para team_a y team_b.
- Permitir partidos placeholder sin equipos definidos.
- Campos mínimos:
  - id
  - tournament_id
  - team_a_id
  - team_b_id
  - starts_at
  - prediction_closes_at
  - stage
  - group
  - status
  - team_a_score
  - team_b_score
  - winner_team_id
  - created_at
  - updated_at

### Fuera de alcance
- No crear pantalla de partidos.
- No crear predicciones.
- No crear scoring.
- No crear admin CRUD.

### Criterios de aceptación
- El modelo Match existe.
- La tabla matches existe.
- Los partidos pueden tener equipos o ser placeholders.
- La relación con Tournament funciona.
- Las relaciones con Team funcionan.

### Commit sugerido
Add matches model and tournament relationships

### Ticket ID
TICKET-006

### Title
Crear seeders iniciales

### Estado
DONE

### Sprint
Sprint 1

### Prioridad
Medium

### Objetivo
Tener datos iniciales suficientes para probar el proyecto localmente.

### Alcance
- Seeder de equipos.
- Seeder de torneo.
- Seeder de partidos.
- Usuario admin.
- Usuario normal.
- Partidos en distintos estados:
  - scheduled
  - open
  - locked
  - finished
  - placeholder

### Fuera de alcance
- No importar datos desde API.
- No cargar fixture completo real todavía.
- No crear pantallas admin.
- No crear predicciones.

### Criterios de aceptación
- php artisan migrate:fresh --seed deja la app usable.
- Hay equipos de prueba.
- Hay un torneo inicial.
- Hay partidos de prueba.
- Hay usuario admin.
- Hay usuario normal.

### Commit sugerido
Add initial development seeders

## EPIC 2 - Usuarios y autenticacion

### Ticket ID
E2-T01

### Title
Configure user authentication

### Status
Todo

### Sprint
Sprint 2

### Priority
High

### Objective
Enable registration, login, logout, and authenticated pages.

### Scope
- Configure Breeze authentication views.
- Ensure mobile-first auth screens.
- Keep copy free of gambling language.

### Out of scope
- Social login.
- Two-factor authentication.
- User profile customization beyond defaults.

### Acceptance criteria
- Users can register, log in, and log out.
- Authenticated routes are protected.
- Auth UI works on mobile and desktop.

### Suggested commit message
feat: configure user authentication

### Ticket ID
TICKET-007

### Title
Ajustar registro de usuario con username

### Estado
DONE

### Sprint
Sprint 2

### Prioridad
High

### Objetivo
Agregar un username visible y unico al registro de usuarios para futuros leaderboards y ligas privadas.

### Alcance
- Agregar columna username a users.
- Hacer username unico.
- Actualizar User fillable.
- Actualizar registro Breeze para solicitar, validar y guardar username.
- Mantener login por email.
- Actualizar seeders con usernames.

### Fuera de alcance
- No agregar rol admin.
- No implementar leaderboards.
- No implementar ligas privadas.
- No modificar comportamiento de login.

### Criterios de aceptación
- php artisan migrate:fresh --seed corre correctamente.
- Register muestra campo username.
- Nuevos usuarios pueden registrarse con username unico.
- Usernames duplicados son rechazados.
- Usuarios seed tienen usernames.
- Login por email sigue funcionando.

### Commit sugerido
Add username to user registration

### Ticket ID
TICKET-008

### Title
Agregar rol admin

### Estado
DONE

### Sprint
Sprint 2

### Prioridad
High

### Objetivo
Agregar la base de rol simple para distinguir usuarios admin de usuarios normales.

### Alcance
- Agregar columna role a users.
- Soportar roles user y admin.
- Definir role user como default.
- Actualizar User fillable.
- Actualizar seeders con admin@example.com como admin y user@example.com como user.
- Agregar helper simple isAdmin().
- Agregar middleware admin basico si corresponde.

### Fuera de alcance
- No crear dashboard admin.
- No crear CRUD admin.
- No crear UI de administracion.
- No implementar scoring.
- No implementar predicciones.
- No implementar ligas privadas.
- No agregar sistema de permisos avanzado.

### Criterios de aceptación
- php artisan migrate:fresh --seed corre correctamente.
- users tiene columna role.
- Nuevos usuarios se registran con role user por defecto.
- Usuario admin seed tiene role admin.
- Usuario normal seed tiene role user.
- Existe una forma simple de verificar si un usuario es admin.

### Commit sugerido
Add admin role to users

## EPIC 3 - Partidos y calendario

### Ticket ID
TICKET-009

### Title
Crear pantalla de próximos partidos

### Estado
DONE

### Sprint
Sprint 2

### Prioridad
High

### Objetivo
Crear una pantalla autenticada donde los usuarios puedan ver próximos partidos y partidos actuales en una interfaz clara mobile-first.

### Alcance
- Crear ruta autenticada GET /matches.
- Crear controlador si corresponde.
- Crear vista Blade para listado de partidos.
- Mostrar equipos o placeholders.
- Mostrar fecha, hora, fase, grupo, estado y resultado si corresponde.
- Agregar enlace desde dashboard.

### Fuera de alcance
- No crear formulario de predicción.
- No crear modelo de predicción.
- No implementar scoring.
- No implementar leaderboard.
- No implementar ligas privadas.
- No implementar admin CRUD.

### Criterios de aceptación
- php artisan migrate:fresh --seed corre correctamente.
- npm run build funciona.
- Usuarios autenticados pueden acceder a /matches.
- Invitados no pueden acceder a /matches.
- Dashboard tiene enlace a la pantalla de partidos.
- Los partidos se muestran con equipos o placeholders.
- Los partidos terminados muestran resultado.

### Commit sugerido
Add matches listing page

### Ticket ID
TICKET-010

### Title
Crear calendario informativo

### Estado
DONE

### Sprint
Sprint 2

### Prioridad
High

### Objetivo
Crear una pantalla autenticada de calendario para ver todos los partidos conocidos del torneo en orden cronológico.

### Alcance
- Crear ruta autenticada GET /calendar.
- Crear controlador si corresponde.
- Crear vista Blade para calendario.
- Mostrar todos los partidos ordenados por starts_at.
- Agrupar partidos por fecha si es simple.
- Mostrar fecha, hora, equipos o placeholders, fase, grupo, estado y resultado si corresponde.
- Agregar enlaces desde dashboard, navegación y pantalla de partidos.

### Fuera de alcance
- No crear formulario de predicción.
- No crear modelo de predicción.
- No implementar scoring.
- No implementar leaderboard.
- No implementar ligas privadas.
- No implementar admin CRUD.
- No usar React, Vue o Inertia.

### Criterios de aceptación
- php artisan migrate:fresh --seed corre correctamente.
- npm run build funciona.
- Usuarios autenticados pueden acceder a /calendar.
- Invitados no pueden acceder a /calendar.
- Dashboard tiene enlace al calendario.
- El calendario muestra todos los partidos seed.
- Los partidos están ordenados cronológicamente.
- Los placeholders se muestran claramente.
- Los partidos terminados muestran resultado.

### Commit sugerido
Add informational tournament calendar

## EPIC 4 - Predicciones

### Ticket ID
TICKET-011

### Title
Crear modelo Prediction

### Estado
DONE

### Sprint
Sprint 2

### Prioridad
High

### Objetivo
Crear el modelo de datos para predicciones de marcador de usuarios.

### Alcance
- Crear modelo Prediction.
- Crear migration predictions.
- Crear PredictionFactory si es util.
- Relacionar Prediction con User.
- Relacionar Prediction con Match.
- Agregar relaciones utiles en User y Match.
- Asegurar una unica prediccion por usuario y partido.

### Fuera de alcance
- No crear formulario de prediccion.
- No crear flujo de envio de predicciones.
- No implementar edicion.
- No implementar scoring.
- No implementar leaderboard.
- No implementar ligas privadas.
- No crear UI de predicciones.

### Criterios de aceptación
- php artisan migrate:fresh --seed corre correctamente.
- Modelo Prediction existe.
- Tabla predictions existe.
- User tiene relacion predictions.
- Match tiene relacion predictions.
- Un usuario no puede duplicar predicciones para el mismo partido a nivel DB.
- No se implementan funcionalidades fuera de alcance.

### Commit sugerido
Add predictions model and migration

### Ticket ID
TICKET-012

### Title
Crear flujo de carga de predicciÃ³n

### Estado
DONE

### Sprint
Sprint 2

### Prioridad
High

### Objetivo
Permitir que un usuario autenticado cargue o actualice su predicciÃ³n de marcador para un partido del torneo.

### Alcance
- Crear rutas autenticadas para ver y guardar predicciones de un partido.
- Crear controlador para el flujo de predicciÃ³n.
- Crear vista Blade para el formulario de predicciÃ³n.
- Permitir cargar team_a_score y team_b_score.
- Actualizar la predicciÃ³n existente del usuario para el mismo partido.
- Validar marcadores enteros entre 0 y 99.
- Bloquear predicciones para placeholders, partidos terminados y partidos cerrados.
- Usar TournamentMatch como modelo Eloquent para la tabla matches.
- Agregar enlace desde la pantalla de partidos para cargar o editar predicciÃ³n.

### Fuera de alcance
- No implementar scoring.
- No implementar leaderboard.
- No implementar ligas privadas.
- No crear admin CRUD.
- No crear historial de predicciones.
- No implementar predicciÃ³n de equipo clasificado en eliminatorias.
- No usar React, Vue o Inertia.

### Criterios de aceptaciÃ³n
- Usuarios autenticados pueden abrir la pantalla de predicciÃ³n de un partido elegible.
- Invitados no pueden acceder a rutas de predicciÃ³n.
- Usuarios pueden cargar una predicciÃ³n valida.
- Usuarios pueden actualizar su predicciÃ³n existente para el mismo partido.
- No se crean predicciones duplicadas para el mismo usuario y partido.
- Partidos placeholder, terminados o cerrados no se pueden predecir.
- Marcadores invalidos son rechazados.
- Las predicciones guardadas quedan en estado submitted.
- points_awarded queda null.

### Commit sugerido
Implement prediction submission flow

### Ticket ID
TICKET-013

### Title
Permitir editar predicciÃ³n antes del cierre

### Estado
DONE

### Sprint
Sprint 2

### Prioridad
High

### Objetivo
Permitir que los usuarios editen una predicciÃ³n existente hasta el cierre de predicciones del partido.

### Nota
Covered by TICKET-012 implementation. Existing predictions can be edited before prediction_closes_at, and edits are blocked after the close time.

### Alcance
- Reutilizar el formulario de predicciÃ³n existente para editar marcadores.
- Precargar la predicciÃ³n existente del usuario.
- Actualizar la predicciÃ³n existente sin crear duplicados.
- Bloquear cambios despuÃ©s de prediction_closes_at.

### Fuera de alcance
- No implementar scoring.
- No implementar historial de cambios.
- No implementar leaderboard.
- No implementar ligas privadas.
- No crear admin CRUD.

### Criterios de aceptaciÃ³n
- Usuarios pueden editar su predicciÃ³n antes de prediction_closes_at.
- Usuarios no pueden editar su predicciÃ³n despuÃ©s de prediction_closes_at.
- No se crean predicciones duplicadas para el mismo usuario y partido.
- Partidos terminados, cerrados o placeholder no se pueden editar.

### Commit sugerido
Allow editing predictions before close

## EPIC 5 - Scoring

### Ticket ID
TICKET-014

### Title
Implementar cÃ¡lculo de puntos base

### Estado
DONE

### Sprint
Sprint 3

### Prioridad
High

### Objetivo
Implementar la lÃ³gica base de cÃ¡lculo de puntos para predicciones de partidos de fase de grupos o partidos estÃ¡ndar.

### Alcance
- Crear un servicio simple y reutilizable para calcular puntos.
- Calcular 6 puntos por resultado exacto.
- Calcular 3 puntos por ganador correcto o empate correcto sin marcador exacto.
- Calcular 0 puntos por predicciÃ³n incorrecta.
- Agregar tests unitarios para los casos principales.
- Mantener la lÃ³gica independiente de UI y de settlement.

### Fuera de alcance
- No implementar formulario admin de resultados.
- No implementar settlement de predicciones.
- No actualizar status scored.
- No actualizar points_awarded en flujo productivo.
- No implementar leaderboard.
- No implementar ligas privadas.
- No implementar scoring de eliminatorias.

### Criterios de aceptaciÃ³n
- Existe un servicio/action de scoring.
- Resultado exacto devuelve 6.
- Ganador o empate correcto sin marcador exacto devuelve 3.
- PredicciÃ³n incorrecta devuelve 0.
- Tests cubren los casos principales.
- No se implementan funcionalidades fuera de alcance.

### Commit sugerido
Add base prediction scoring logic

### Ticket ID
E5-T01

### Title
Calculate prediction points

### Status
Todo

### Sprint
Sprint 3

### Priority
High

### Objective
Award points based on real match results and prediction accuracy.

### Scope
- Store real match scores.
- Calculate 6, 3, or 0 points for standard matches.
- Make recalculation idempotent.
- Add tests for exact result, correct winner, correct draw, and incorrect prediction.

### Out of scope
- Knockout qualified-team scoring.
- Admin UI for recalculation.

### Acceptance criteria
- Exact result earns 6 points.
- Correct winner or correct draw without exact score earns 3 points.
- Incorrect prediction earns 0 points.
- Recalculating does not duplicate points.

### Suggested commit message
feat: calculate prediction points

## EPIC 6 - Leaderboard

### Ticket ID
E6-T01

### Title
Show general leaderboard

### Status
Todo

### Sprint
Sprint 3

### Priority
High

### Objective
Rank all users by total points in the general league.

### Scope
- Add general leaderboard page.
- Show user rank and total points.
- Include all registered users automatically.

### Out of scope
- Private league leaderboards.
- Tie-breaker complexity beyond simple stable ordering.

### Acceptance criteria
- All users appear in the general leaderboard.
- Users are ordered by total points descending.
- The page is usable on mobile.

### Suggested commit message
feat: show general leaderboard

## EPIC 7 - Ligas privadas

### Ticket ID
E7-T01

### Title
Create private leagues

### Status
Todo

### Sprint
Sprint 4

### Priority
High

### Objective
Allow users to create one private league with a unique visible code.

### Scope
- Add private league model, migration, and owner relationship.
- Generate a unique visible ID or code.
- Enforce at most 1 owned private league per user.
- Allow duplicate league names.

### Out of scope
- Join requests.
- Member removal.
- League leaderboard.

### Acceptance criteria
- A user can create one private league.
- A second league creation attempt is blocked.
- Duplicate league names are allowed.
- Each league has a unique visible code.

### Suggested commit message
feat: add private league creation

### Ticket ID
E7-T02

### Title
Show private league leaderboard

### Status
Todo

### Sprint
Sprint 4

### Priority
Medium

### Objective
Rank members inside each private league.

### Scope
- Add league detail page.
- Show members ordered by total points.
- Ensure only league members can view private league details.

### Out of scope
- Join request workflow.
- Member removal workflow.

### Acceptance criteria
- Members can view their private league leaderboard.
- Non-members cannot view private league details.
- Ranking uses the same points total as the general leaderboard.

### Suggested commit message
feat: show private league leaderboard

## EPIC 8 - Invitaciones y solicitudes

### Ticket ID
E8-T01

### Title
Search and request to join private leagues

### Status
Todo

### Sprint
Sprint 5

### Priority
High

### Objective
Let users find private leagues and request owner approval to join.

### Scope
- Search leagues by name or visible ID/code.
- Create join request records.
- Let owners approve or reject requests.
- Enforce at most 3 private league memberships per user.

### Out of scope
- Email notifications.
- Public invitation links.

### Acceptance criteria
- Users can search by league name.
- Users can search by league ID/code.
- Joining requires owner approval.
- Users cannot exceed 3 private league memberships.

### Suggested commit message
feat: add private league join requests

### Ticket ID
E8-T02

### Title
Remove private league members with audit log

### Status
Todo

### Sprint
Sprint 5

### Priority
Medium

### Objective
Allow league owners to remove members while logging the action.

### Scope
- Add owner-only member removal.
- Add audit log entry for each removal.
- Preserve enough log details to understand who removed whom and when.

### Out of scope
- Reinstatement workflow.
- Notifications.

### Acceptance criteria
- Owners can remove members from their league.
- Non-owners cannot remove members.
- Each removal creates a log entry.

### Suggested commit message
feat: log private league member removals

## EPIC 9 - Admin

### Ticket ID
E9-T01

### Title
Build admin tournament management

### Status
Todo

### Sprint
Sprint 5

### Priority
High

### Objective
Allow admin users to manage teams, phases, matches, and real results.

### Scope
- Add admin dashboard.
- Add CRUD screens for teams, phases, and matches.
- Add result entry/update workflow.
- Show current environment and test/live mode.

### Out of scope
- Advanced import/export.
- Non-admin management roles.

### Acceptance criteria
- Admin users can manage tournament records.
- Regular users cannot access admin screens.
- Admin dashboard shows environment and mode.
- Result changes can trigger or prepare ranking recalculation.

### Suggested commit message
feat: add admin tournament management

### Ticket ID
E9-T02

### Title
Add ranking recalculation admin action

### Status
Todo

### Sprint
Sprint 5

### Priority
High

### Objective
Let admins recalculate rankings after result changes.

### Scope
- Add admin action to recalculate prediction points.
- Show success/failure feedback.
- Add tests for recalculation behavior.

### Out of scope
- Background queues.
- Scheduled jobs.

### Acceptance criteria
- Admin can trigger recalculation.
- Recalculation is idempotent.
- Leaderboards reflect updated points.

### Suggested commit message
feat: add admin ranking recalculation

## EPIC 10 - Fase eliminatoria

### Ticket ID
E10-T01

### Title
Support knockout placeholders

### Status
Todo

### Sprint
Sprint 6

### Priority
High

### Objective
Represent knockout matches before both teams are known.

### Scope
- Allow matches with placeholder labels.
- Prevent predictions until both teams are assigned.
- Let admins assign teams later.

### Out of scope
- Bracket visualization.
- Automatic advancement.

### Acceptance criteria
- Placeholder matches can be created.
- Placeholder matches appear in the calendar.
- Users cannot submit predictions until both teams are known.

### Suggested commit message
feat: support knockout match placeholders

### Ticket ID
E10-T02

### Title
Add knockout qualified-team predictions

### Status
Todo

### Sprint
Sprint 6

### Priority
High

### Objective
Allow users to predict score and qualified team for knockout matches.

### Scope
- Extend prediction form for knockout matches.
- Store predicted qualified team.
- Score knockout predictions using 6, 3, or 0 point rules.

### Out of scope
- Penalty shootout score details.
- Automatic bracket propagation.

### Acceptance criteria
- Users can choose a qualified team for ready knockout matches.
- Exact score plus correct qualified team earns 6 points.
- Correct qualified team without exact score earns 3 points.
- Incorrect prediction earns 0 points.

### Suggested commit message
feat: add knockout prediction scoring

## EPIC 11 - UX / UI

### Ticket ID
E11-T01

### Title
Polish mobile-first user experience

### Status
Todo

### Sprint
Sprint 7

### Priority
Medium

### Objective
Improve the main user flows for small screens and responsive layouts.

### Scope
- Review auth, match calendar, predictions, leaderboards, and private league screens.
- Improve spacing, navigation, forms, and empty states.
- Keep Blade, Tailwind, and vanilla JavaScript.

### Out of scope
- Full redesign.
- New frontend frameworks.

### Acceptance criteria
- Main flows are usable on common mobile widths.
- UI copy avoids gambling language.
- Desktop layouts remain clean and readable.

### Suggested commit message
style: polish mobile-first user flows

## EPIC 12 - Testing y hardening

### Ticket ID
TICKET-TECH-001

### Title
Configurar entorno local de tests

### Status
Todo

### Sprint
Tech follow-up

### Priority
Medium

### Objective
Configurar el entorno local para que la suite completa de tests pueda ejecutarse sin fallas de driver de base de datos.

### Context
The specific scoring tests pass with:
`php artisan test --filter=PredictionScoringServiceTest`

However, the full test suite currently fails locally because PHPUnit/Breeze feature tests try to use sqlite `:memory:`, and the local PHP installation does not have the SQLite driver enabled.

This is a local testing environment/configuration issue, not a scoring logic issue.

### Scope
- Configure tests to use a dedicated MySQL testing database.
- Or enable the SQLite driver locally.
- Document the chosen local testing setup.

### Out of scope
- Application feature changes.
- Scoring logic changes.
- Authentication flow changes.
- UI changes.

### Acceptance criteria
- The full local test suite can run successfully.
- The chosen test database strategy is documented.
- Scoring tests continue to pass.

### Suggested commit message
Configure local test environment

### Ticket ID
E12-T01

### Title
Add v1 feature coverage

### Status
Todo

### Sprint
Sprint 8

### Priority
High

### Objective
Cover critical v1 behavior with tests.

### Scope
- Add tests for auth access, prediction deadlines, scoring, private league limits, join approvals, removals, and admin access.
- Add regression tests for knockout placeholders and knockout scoring.

### Out of scope
- Exhaustive browser automation.
- Load testing.

### Acceptance criteria
- Critical domain rules have test coverage.
- Test suite passes locally.
- Risky edge cases are documented or covered.

### Suggested commit message
test: cover critical v1 rules

### Ticket ID
E12-T02

### Title
Harden validation and authorization

### Status
Todo

### Sprint
Sprint 8

### Priority
High

### Objective
Review validation and authorization across v1 workflows.

### Scope
- Validate forms and route access.
- Check ownership rules for private leagues.
- Check admin-only actions.
- Review user-facing errors.

### Out of scope
- Security audit by a third party.
- Rate limiting beyond Laravel defaults unless needed.

### Acceptance criteria
- Users cannot modify records they do not own.
- Admin actions are protected.
- Validation errors are understandable.

### Suggested commit message
fix: harden validation and authorization

## EPIC 13 - Deploy / Railway

### Ticket ID
E13-T01

### Title
Prepare Railway deployment

### Status
Todo

### Sprint
Sprint 8

### Priority
High

### Objective
Prepare the Laravel app for deployment on Railway with Caddy / FrankenPHP.

### Scope
- Add Railway deployment configuration.
- Configure Caddy / FrankenPHP as needed.
- Document required environment variables.
- Verify production build steps.

### Out of scope
- Custom Kubernetes setup.
- Multi-service architecture.

### Acceptance criteria
- Deployment configuration is documented.
- App can be deployed to Railway.
- Environment variables are clear.
- Test/live mode is visible in admin.

### Suggested commit message
chore: prepare railway deployment
