# Prode Mundial 2026 - v1 Backlog

Last updated: 2026-06-05

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
Done

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
Crear modelo TournamentMatch

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
- Crear modelo TournamentMatch.
- Mantener la tabla de base de datos como matches.
- Usar App\Models\TournamentMatch como modelo Eloquent.
- Relacionar TournamentMatch con Tournament.
- Relacionar TournamentMatch con Team para team_a y team_b.
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
- El modelo TournamentMatch existe.
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
Done

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
E3-T03B

### Title
Email verification by code

### Status
Done

### Sprint
Sprint 8

### Priority
High

### Objective
Require new email/password users to verify their email with a short code before entering the main app.

### Note
Implemented with a dedicated `email_verification_codes` table that stores hashed 6-digit codes, expires codes after 15 minutes, and marks older active codes as used when a new code is sent. Registration now sends a Spanish Prode transactional email through Laravel Mail and redirects to `/email/verify-code`. Main authenticated app areas require verified email, while the code screen, submit, resend, and logout remain available to unverified users. Google-created or Google-linked users are treated as verified, and staging/demo users remain verified for QA.

### Scope
- Store hashed email verification codes with expiration and used state.
- Send Spanish verification-code email through Laravel Mail.
- Add code verification, resend, and verification screen routes.
- Use `users.email_verified_at` as the verification state.
- Gate dashboard, predictions, leagues, calendar, history, profile, and admin routes behind verified email.
- Keep demo users verified in seed/reset data.
- Add focused feature tests with `Mail::fake()`.
- Update staging QA documentation.

### Out of scope
- No Brevo secrets or SMTP credentials.
- No removal of traditional email/password login.
- No changes to prediction, scoring, league, or admin business logic beyond the verified-email access gate.
- No external packages.
- No React, Vue, or Inertia.

### Acceptance criteria
- New registered users must verify email by code before accessing the app.
- Verification codes are not stored in plaintext.
- Expired, invalid, and used codes fail.
- Resend sends a new code and supersedes previous active codes.
- Demo users remain verified for local/staging QA.
- Automated tests do not send real email.

### Suggested commit message
Add email verification by code

### Ticket ID
E3-T04

### Title
Registration and verification email abuse protection

### Status
Done

### Sprint
Sprint 8

### Priority
High

### Objective
Protect the Brevo transactional email quota from bot-driven registration and verification-code resend abuse.

### Note
Implemented with configurable cache counters and Laravel rate limiting. Registration now has a hidden honeypot, an hourly per-IP limit, and a global daily registration cap. Verification-code email sending checks a global daily cap before creating a code or calling Brevo. Resend actions enforce a per-user cooldown plus hourly and daily limits. Optional admin alerts use the Brevo HTTP API and are rate-limited per alert type.

### Scope
- Add abuse protection configuration with safe defaults.
- Add registration honeypot and registration rate limits.
- Add global daily verification email cap before Brevo API calls.
- Add resend cooldown and per-user resend caps.
- Add optional rate-limited admin alert emails.
- Add warning/info logging without secrets or verification codes.
- Add focused feature tests for registration, verification cap, resend limits, and alert cooldown.
- Update staging QA documentation.

### Out of scope
- No captcha.
- No scoring, prediction, league, or API-Football changes.
- No SMTP dependency for verification or alert emails.
- No external packages.

### Acceptance criteria
- Normal registration and email verification still work.
- Abuse limits prevent excessive registration and resend attempts.
- Daily verification email cap prevents Brevo API calls after the limit.
- User-facing errors stay friendly and Spanish.
- Automated tests do not call real Brevo.

### Suggested commit message
Add registration abuse protection

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
Crear flujo de carga de predicción

### Estado
DONE

### Sprint
Sprint 2

### Prioridad
High

### Objetivo
Permitir que un usuario autenticado cargue o actualice su predicción de marcador para un partido del torneo.

### Nota
The single-match prediction flow remains available, but the primary UX will move to TICKET-014B.

### Alcance
- Crear rutas autenticadas para ver y guardar predicciones de un partido.
- Crear controlador para el flujo de predicción.
- Crear vista Blade para el formulario de predicción.
- Permitir cargar team_a_score y team_b_score.
- Actualizar la predicción existente del usuario para el mismo partido.
- Validar marcadores enteros entre 0 y 99.
- Bloquear predicciones para placeholders, partidos terminados y partidos cerrados.
- Usar TournamentMatch como modelo Eloquent para la tabla matches.
- Agregar enlace desde la pantalla de partidos para cargar o editar predicción.

### Fuera de alcance
- No implementar scoring.
- No implementar leaderboard.
- No implementar ligas privadas.
- No crear admin CRUD.
- No crear historial de predicciones.
- No implementar predicción de equipo clasificado en eliminatorias.
- No usar React, Vue o Inertia.

### Criterios de aceptación
- Usuarios autenticados pueden abrir la pantalla de predicción de un partido elegible.
- Invitados no pueden acceder a rutas de predicción.
- Usuarios pueden cargar una predicción valida.
- Usuarios pueden actualizar su predicción existente para el mismo partido.
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
Permitir editar predicción antes del cierre

### Estado
DONE

### Sprint
Sprint 2

### Prioridad
High

### Objetivo
Permitir que los usuarios editen una predicción existente hasta el cierre de predicciones del partido.

### Nota
Covered by TICKET-012 implementation. Existing predictions can be edited before prediction_closes_at, and edits are blocked after the close time.

### Alcance
- Reutilizar el formulario de predicción existente para editar marcadores.
- Precargar la predicción existente del usuario.
- Actualizar la predicción existente sin crear duplicados.
- Bloquear cambios después de prediction_closes_at.

### Fuera de alcance
- No implementar scoring.
- No implementar historial de cambios.
- No implementar leaderboard.
- No implementar ligas privadas.
- No crear admin CRUD.

### Criterios de aceptación
- Usuarios pueden editar su predicción antes de prediction_closes_at.
- Usuarios no pueden editar su predicción después de prediction_closes_at.
- No se crean predicciones duplicadas para el mismo usuario y partido.
- Partidos terminados, cerrados o placeholder no se pueden editar.

### Commit sugerido
Allow editing predictions before close

## EPIC 5 - Scoring

### Ticket ID
TICKET-014

### Title
Implementar cálculo de puntos base

### Estado
DONE

### Sprint
Sprint 3

### Prioridad
High

### Objetivo
Implementar la lógica base de cálculo de puntos para predicciones de partidos de fase de grupos o partidos estándar.

### Alcance
- Crear un servicio simple y reutilizable para calcular puntos.
- Calcular 6 puntos por resultado exacto.
- Calcular 3 puntos por ganador correcto o empate correcto sin marcador exacto.
- Calcular 0 puntos por predicción incorrecta.
- Agregar tests unitarios para los casos principales.
- Mantener la lógica independiente de UI y de settlement.

### Fuera de alcance
- No implementar formulario admin de resultados.
- No implementar settlement de predicciones.
- No actualizar status scored.
- No actualizar points_awarded en flujo productivo.
- No implementar leaderboard.
- No implementar ligas privadas.
- No implementar scoring de eliminatorias.

### Criterios de aceptación
- Existe un servicio/action de scoring.
- Resultado exacto devuelve 6.
- Ganador o empate correcto sin marcador exacto devuelve 3.
- Predicción incorrecta devuelve 0.
- Tests cubren los casos principales.
- No se implementan funcionalidades fuera de alcance.

### Commit sugerido
Add base prediction scoring logic

### Ticket ID
TICKET-014B

### Title
Crear pantalla de predicciones inline por día

### Estado
DONE

### Sprint
Sprint 3

### Prioridad
Alta

### Objetivo
Crear la experiencia principal de carga de predicciones, permitiendo que el usuario cargue o edite varios pronósticos directamente desde una lista agrupada por día, sin tener que entrar partido por partido.

### Alcance
- Crear una pantalla autenticada de predicciones inline.
- Suggested route:
  - GET /predictions
  - route name: predictions.index
- Mostrar partidos agrupados por fecha.
- Mostrar equipos, horario, fase, grupo y estado.
- Mostrar inputs inline para team_a_score y team_b_score cuando el partido sea predecible.
- Precargar valores si el usuario ya tiene una predicción.
- Mostrar partidos no predecibles como solo lectura.
- Mostrar placeholders claramente.
- Agregar un botón flotante "Guardar cambios" que aparezca cuando el usuario modifique algún input.
- Guardar múltiples predicciones modificadas en una sola acción.
- Suggested route:
  - POST /predictions/bulk
  - route name: predictions.bulk-store
- Reutilizar validaciones existentes.
- Reutilizar TournamentMatch::isPredictable().
- Reutilizar updateOrCreate para mantener una predicción por usuario/partido.
- Mantener la vista individual de predicción como fallback por ahora, salvo que sea necesario simplificar.
- Agregar link desde dashboard y/o matches hacia la nueva pantalla principal de predicciones.

### Fuera de alcance
- No implementar scoring nuevo.
- No implementar leaderboard.
- No implementar private leagues.
- No implementar admin CRUD.
- No implementar resultados reales.
- No implementar predicción de clasificado en eliminatorias.
- No implementar API externa.
- No agregar frameworks frontend.
- No usar React, Vue o Inertia.
- No agregar dependencias innecesarias.

### Criterios de aceptación
- php artisan migrate:fresh --seed corre correctamente.
- npm run build corre correctamente.
- Usuario autenticado puede acceder a /predictions.
- Invitados no pueden acceder a /predictions.
- Los partidos aparecen agrupados por día.
- Los partidos predecibles muestran inputs inline.
- Las predicciones existentes aparecen precargadas.
- Al modificar inputs aparece un botón flotante para guardar.
- Se pueden guardar varias predicciones en una sola acción.
- No se guardan predicciones para partidos cerrados, locked, finished o placeholder.
- Los errores de validación se muestran de forma clara.
- No se duplica una predicción para el mismo usuario/partido.
- No se implementan funcionalidades fuera de alcance.

### Commit sugerido
Add inline daily predictions page

### Ticket ID
TICKET-015A

### Title
Crear listado admin mínimo de partidos

### Estado
DONE

### Sprint
Sprint 3

### Prioridad
Alta

### Objetivo
Crear una pantalla admin mínima para listar partidos y preparar la futura carga de resultados reales.

### Nota
Implemented and committed. Admin users can access /admin/matches, normal users receive 403, guests are redirected, and matches are listed with status/result/action placeholders.

### Alcance
- Crear una ruta admin autenticada y protegida por rol admin.
- Suggested route:
  - GET /admin/matches
  - route name: admin.matches.index
- Mostrar listado de partidos.
- Mostrar equipos o placeholders.
- Mostrar fecha y hora.
- Mostrar fase y grupo si existen.
- Mostrar estado del partido.
- Mostrar resultado si ya existe.
- Mostrar una acción visual futura para "Cargar resultado" o "Editar resultado", pero sin implementar todavía el guardado.
- Reutilizar el middleware admin existente.
- Mantener diseño simple, mobile-first y alineado con docs/ui-guidelines.md.

### Fuera de alcance
- No cargar resultados todavía.
- No editar partidos.
- No crear partidos.
- No eliminar partidos.
- No aplicar scoring.
- No recalcular rankings.
- No crear leaderboard.
- No gestionar equipos.
- No gestionar usuarios.
- No crear CRUD admin completo.

### Criterios de aceptación
- Solo usuarios admin pueden acceder a /admin/matches.
- Usuarios normales no pueden acceder.
- Invitados son redirigidos al login.
- El admin puede ver todos los partidos.
- Se muestran estado, equipos/placeholders, fecha, fase/grupo y resultado si existe.
- No se implementan funcionalidades fuera de alcance.

### Commit sugerido
Add admin matches listing

### Ticket ID
TICKET-015B

### Title
Cargar o corregir resultado manualmente

### Estado
DONE

### Sprint
Sprint 3

### Prioridad
Alta

### Objetivo
Permitir que un admin cargue o edite manualmente el resultado real de un partido como herramienta de fallback o corrección. La fuente principal esperada para resultados reales será una integración con API externa en un ticket posterior. La carga manual existe para operar la plataforma durante desarrollo, corregir resultados incorrectos o recuperarse si la API falla o se demora.

### Alcance
- Crear pantalla/formulario admin para cargar o corregir manualmente un resultado.
- Tratar esta funcionalidad como fallback/corrección.
- No asumir que todos los resultados se cargarán manualmente en producción.
- Suggested routes:
  - GET /admin/matches/{tournamentMatch}/result
  - POST /admin/matches/{tournamentMatch}/result
- Validar goles reales:
  - enteros
  - mínimo 0
  - máximo 99
- Guardar team_a_score y team_b_score.
- Definir winner_team_id si hay ganador.
- Si hay empate, winner_team_id debe quedar null.
- Cambiar estado del partido a finished.
- Mostrar resultado en admin matches.
- No aplicar scoring todavía en este ticket.

### Fuera de alcance
- No puntuar predicciones todavía.
- No recalcular rankings.
- No leaderboard.
- No penalties/qualified team logic.
- No edición general de partido.
- No API externa.

### Criterios de aceptación
- Solo admin puede cargar resultados.
- Resultado se guarda correctamente.
- Partido cambia a finished.
- Winner team se guarda si corresponde.
- Empate deja winner_team_id null.
- No se aplica scoring todavía.
- No se implementan funcionalidades fuera de alcance.

### Commit sugerido
Add admin match result form

### Ticket ID
TICKET-015C

### Title
Aplicar scoring a predicciones al finalizar partido

### Estado
DONE

### Sprint
Sprint 3

### Prioridad
Alta

### Objetivo
Aplicar la lógica de scoring existente a las predicciones cuando un partido ya tiene resultado real.

### Alcance
- Reutilizar PredictionScoringService.
- Puntuar todas las predicciones submitted/locked del partido.
- Guardar points_awarded.
- Cambiar status de predicciones a scored.
- Evitar duplicaciones o acumulaciones incorrectas.
- Permitir re-ejecutar scoring de forma segura si el resultado se corrige.
- Integrar el scoring al flujo de carga de resultado si corresponde.

### Fuera de alcance
- No leaderboard visual todavía.
- No rankings por liga.
- No knockout/penalties scoring.
- No notificaciones.
- No API externa.

### Criterios de aceptación
- Predicción exacta recibe 6 puntos.
- Tendencia correcta recibe 3 puntos.
- Incorrecta recibe 0 puntos.
- Predicciones quedan status scored.
- Recalcular no duplica puntos.
- Tests o verificación manual cubren el flujo.
- No se implementan funcionalidades fuera de alcance.

### Commit sugerido
Apply scoring when match is finished

### Ticket ID
TICKET-016

### Title
Crear historial de predicciones del usuario

### Estado
DONE

### Sprint
Sprint 3

### Prioridad
Alta

### Objetivo
Permitir que el usuario vea sus predicciones realizadas, el resultado real del partido cuando exista y los puntos obtenidos.

### Alcance
- Crear una pantalla autenticada de historial de predicciones.
- Suggested route:
  - GET /my-predictions
  - route name: predictions.history
- Mostrar solo predicciones del usuario autenticado.
- Mostrar partido.
- Mostrar equipos o placeholders.
- Mostrar fecha y hora del partido.
- Mostrar predicción del usuario.
- Mostrar resultado real si el partido está finished.
- Mostrar estado de la predicción.
- Mostrar puntos obtenidos si points_awarded no es null.
- Mostrar estado pendiente si todavía no fue puntuada.
- Ordenar por fecha del partido, preferentemente más recientes primero.
- Agregar link desde dashboard y/o pantalla de predicciones.
- Mantener diseño simple, mobile-first y alineado con docs/ui-guidelines.md.

### Fuera de alcance
- No crear leaderboard.
- No crear ranking global.
- No crear ligas privadas.
- No crear scoring nuevo.
- No permitir editar desde historial.
- No cargar resultados reales.
- No admin CRUD.
- No API externa.
- No agregar dependencias frontend.

### Criterios de aceptación
- php artisan migrate:fresh --seed corre correctamente.
- npm run build corre correctamente.
- Usuario autenticado puede acceder a /my-predictions.
- Invitados son redirigidos al login.
- El usuario ve solo sus propias predicciones.
- Se muestra la predicción realizada.
- Se muestra resultado real si existe.
- Se muestran puntos cuando la predicción fue puntuada.
- Se muestra estado pendiente cuando no fue puntuada.
- No se implementan funcionalidades fuera de alcance.

### Commit sugerido
Add user prediction history

### Ticket ID
TICKET-017

### Title
Crear leaderboard general

### Estado
DONE

### Sprint
Sprint 3

### Prioridad
Alta

### Objetivo
Crear el ranking general de usuarios según los puntos obtenidos en sus predicciones puntuadas.

### Alcance
- Crear una pantalla pública o autenticada de leaderboard general.
- Suggested route:
  - GET /leaderboard
  - route name: leaderboard.index
- Mostrar ranking de usuarios.
- Calcular puntos totales sumando points_awarded de predicciones scored.
- Mostrar posición.
- Mostrar username.
- Mostrar puntos totales.
- Mostrar cantidad de resultados exactos acertados.
- Mostrar cantidad de tendencias acertadas.
- Mostrar cantidad de predicciones puntuadas.
- Ordenar por puntos totales descendente.
- Destacar visualmente el primer puesto.
- Agregar link desde dashboard y/o navegación principal.
- Mantener diseño mobile-first y alineado con docs/ui-guidelines.md.

### Fuera de alcance
- No crear ligas privadas.
- No crear leaderboard por liga.
- No crear sistema avanzado de desempate.
- No crear rankings persistidos en tabla separada.
- No crear badges.
- No crear premios.
- No modificar scoring.
- No agregar API externa.

### Criterios de aceptación
- php artisan migrate:fresh --seed corre correctamente.
- npm run build corre correctamente.
- Usuario puede acceder a /leaderboard.
- Se muestran usuarios con predicciones puntuadas.
- Se ordena por puntos descendente.
- Se muestran puntos totales.
- Se muestran exactos.
- Se muestran tendencias.
- Se muestran predicciones puntuadas.
- El primer puesto se destaca visualmente.
- No se implementan funcionalidades fuera de alcance.

### Commit sugerido
Add global leaderboard

### Ticket ID
E5-T01

### Title
Calculate prediction points

### Status
Done

### Sprint
Sprint 3

### Priority
High

### Objective
Award points based on real match results and prediction accuracy.

### Note
Covered by TICKET-014 and TICKET-015C. Base scoring and idempotent settlement are already implemented.

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
Done

### Sprint
Sprint 3

### Priority
High

### Objective
Rank all users by total points in the general league.

### Note
Covered by TICKET-017. The authenticated global leaderboard is already implemented, ranks users by total scored points, shows exact results, trends and scored prediction count, and is mobile-friendly.

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
Done

### Sprint
Sprint 4

### Priority
High

### Objective
Allow users to create one private league with a unique visible code.

### Note
Implemented with PrivateLeague model, unique visible code, unique owner constraint, owner relationship, create/store/show routes, owner-only detail page, and dashboard/navigation links.

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
Done

### Sprint
Sprint 4

### Priority
Medium

### Objective
Rank members inside each private league.

### Note
Implemented after E8-T01. Private league detail now shows a leaderboard for active members only, using the same scoring aggregation as the global leaderboard, with owner/member access control and non-member blocking.

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
Done

### Note
Implemented with LeagueMembership, LeagueJoinRequest, league search by name/code, join requests, owner approval/rejection, owner auto-membership, member-only league detail access, duplicate request blocking, own-league request blocking, active-member blocking, and max 5 active private league memberships.

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
Done

### Note
Implemented with LeagueAuditLog, owner-only member removal, membership status changed to removed, joined_at preserved, member_removed audit entries, owner-only remove actions, recent removal activity for owner, removed members blocked from league detail, and removed members excluded from private league leaderboard.

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

### Ticket ID
E8-T03

### Title
Add private league invitation links

### Status
Done

### Note
Implemented with invite route using visible league code, owner share/copy section, invitation page, reuse of existing join request flow, authenticated access, states for owner, active member, pending request, removed user, max 5 leagues, and new requester. Invitation links do not bypass owner approval.

### Sprint
Sprint 5

### Priority
High

### Objective
Allow private league owners to share a direct invitation link so other users can find the league and request access more easily.

### Scope
- Add a visible invitation link/button on the private league detail page for the owner.
- The link should point to the league detail or a dedicated join/request page.
- The owner should be able to copy the invitation link.
- Users who open the link should be able to request access if eligible.
- If the user is not authenticated, they should be redirected to login and then be able to continue.
- If the user is already an active member, show the league detail.
- If the user already has a pending request, show a clear pending state.
- If the user was removed, do not automatically re-add them.
- Joining through the link must still require owner approval.
- Reuse the existing LeagueJoinRequest flow.
- Reuse existing business rules:
  - no duplicate pending requests
  - no request to own league
  - no request if already active member
  - max 5 active private league memberships

### Out of scope
- No automatic joining without approval.
- No email invitations.
- No notifications.
- No public league directory.
- No member removal changes.
- No scoring changes.
- No leaderboard changes.
- No API integration.
- No external packages.

### Acceptance criteria
- Owner can see and copy an invitation link.
- Authenticated non-members can open the link and request access.
- Guests are redirected to login.
- Existing members opening the link see the league detail.
- Users with pending requests see pending state.
- Removed users are not automatically re-added.
- Owner approval is still required.
- No out-of-scope features are implemented.

### Suggested commit message
Add private league invitation links

## EPIC 9 - Admin

### Ticket ID
E9-T01A

### Title
Create minimal admin dashboard

### Status
Done

### Note
Implemented with /admin route, admin-only dashboard, environment and app mode display, operational counts, quick links, and admin-only navigation link. E9-T01 remains for full admin tournament management.

### Sprint
Sprint 5

### Priority
High

### Objective
Create a minimal admin dashboard to centralize operational access and show current environment and test/live mode.

### Scope
- Add an admin-only dashboard route:
  - GET /admin
  - route name: admin.dashboard
- Show current application environment:
  - APP_ENV or equivalent
- Show current application mode:
  - test/live or equivalent
- If APP_MODE does not exist yet, document or add a safe fallback value like test.
- Show quick operational links:
  - admin matches
  - global leaderboard
  - private leagues overview if it exists, otherwise placeholder text
  - manual result fallback if relevant via admin matches
- Show basic counts if simple:
  - users
  - matches
  - predictions
  - private leagues
- Add admin-only navigation link to the admin dashboard.
- Keep UI mobile-first and aligned with docs/ui-guidelines.md.
- Use product language:
  - administracion
  - entorno
  - modo prueba
  - modo live
  - partidos
  - resultados
  - ligas
  - ranking
  Avoid gambling/betting language.

### Out of scope
- No full admin CRUD.
- No team management.
- No match editing.
- No user management.
- No private league admin management.
- No scoring changes.
- No API integration.
- No deploy configuration.
- No external packages.

### Acceptance criteria
- php artisan migrate:fresh --seed passes.
- npm run build passes.
- Admin can access /admin.
- Normal users cannot access /admin.
- Guests are redirected to login.
- Admin dashboard shows current environment.
- Admin dashboard shows test/live mode or safe fallback.
- Admin dashboard links to admin matches.
- No out-of-scope features are implemented.

### Suggested commit message
Add minimal admin dashboard

### Ticket ID
E9-T01

### Title
Build admin tournament management

### Status
Done

### Sprint
Sprint 5

### Priority
High

### Objective
Allow admin users to manage teams, phases, matches, and real results.

### Note
Partially covered by TICKET-015A and TICKET-015B. Remaining scope includes admin dashboard, team CRUD, match CRUD, phase management, and environment/test-live mode visibility.
Partially covered by E9-T01A once implemented. E9-T01 remains for full admin tournament management.

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
Done

### Sprint
Sprint 5

### Priority
High

### Objective
Let admins recalculate rankings after result changes.

### Note
Partially covered by TICKET-015C because result corrections already trigger idempotent prediction rescoring. Keep this ticket only for a future explicit admin recalculation action if still needed.

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
Done

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

> Note: Covered by existing placeholder support and E10-T01A. Placeholder matches can exist, appear in calendar/listings, block predictions until teams are assigned, and admin can assign teams later.

### Ticket ID
E10-T01A

### Title
Assign teams to knockout placeholder matches

### Status
Done

### Sprint
Sprint 6

### Priority
High

### Objective
Allow admin users to assign teams to placeholder knockout matches so they can become real predictable fixtures once teams are known.

> Note: Implemented with admin-only team assignment routes, team selection form, validation for existing and different teams, placeholder-to-scheduled status update, admin matches action, and tests for guest redirect, normal user block, admin form access, successful assignment and distinct team validation.

### Scope
- Add admin-only form to assign or update team_a_id and team_b_id for an existing TournamentMatch.
- Suggested route:
  - GET /admin/matches/{tournamentMatch}/teams
  - route name: admin.matches.teams.edit
  - POST /admin/matches/{tournamentMatch}/teams
  - route name: admin.matches.teams.update
- Allow assigning teams only from existing Team records.
- Validate:
  - team_a_id required
  - team_b_id required
  - both must exist in teams
  - team_a_id and team_b_id must be different
- When both teams are assigned:
  - update status from placeholder to scheduled or open, using the safest existing convention
  - keep starts_at and prediction_closes_at unchanged unless there is a clear reason to update them
- Update admin matches listing to show an action like "Asignar equipos" for placeholder or missing-team matches.
- Once teams are assigned, existing prediction screens should naturally allow predictions if TournamentMatch::isPredictable() allows it.
- Keep UI simple, mobile-first and aligned with docs/ui-guidelines.md.

### Out of scope
- No bracket visualization.
- No automatic advancement.
- No API integration.
- No knockout qualified-team prediction.
- No scoring changes.
- No result loading changes except preserving existing result flow.
- No team CRUD.
- No match CRUD beyond assigning teams to an existing placeholder match.
- No external packages.

### Acceptance criteria
- php artisan test passes.
- php artisan migrate:fresh --seed passes.
- npm run build passes.
- Admin can open team assignment form for a placeholder/missing-team match.
- Admin can assign two different existing teams.
- Match team_a_id and team_b_id are saved.
- Match status changes from placeholder to scheduled/open.
- Normal users cannot access team assignment routes.
- Guests are redirected to login.
- Predictions become available once teams are assigned and match is otherwise predictable.
- No out-of-scope features are implemented.

### Suggested commit message
Add admin team assignment for placeholder matches

### Ticket ID
E10-T02

### Title
Add knockout qualified-team predictions

### Status
Done

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

### Note
Covered by E10-T02A and E10-T02B. The data model, UI flow, validation, persistence and scoring for knockout qualified-team predictions are implemented.

### Ticket ID
E10-T02A

### Title
Add qualified team prediction data model

### Status
Done

### Sprint
Sprint 6

### Priority
High

### Objective
Prepare the data model for knockout predictions by allowing users to store the team they believe will qualify.

### Scope
- Add a nullable predicted_qualified_team_id field to predictions.
- Add foreign key to teams.
- Update Prediction model fillable/casts/relationships as appropriate.
- Add helper relationship predictedQualifiedTeam if useful.
- Add TournamentMatch helper methods:
  - isKnockout()
  - requiresQualifiedTeamPrediction()
- Determine knockout status based on stage values.
- Keep existing group-stage prediction behavior unchanged.

### Out of scope
- No UI changes.
- No scoring changes.
- No controller validation changes.
- No leaderboard changes.
- No history UI changes.
- No bracket visualization.
- No penalty shootout detail.

### Acceptance criteria
- php artisan test passes.
- php artisan migrate:fresh --seed passes.
- predictions table has predicted_qualified_team_id.
- Prediction model supports predicted qualified team relationship.
- TournamentMatch can identify knockout matches.
- Existing group-stage prediction behavior is not changed.

### Note
Implemented with predictions.predicted_qualified_team_id, foreign key to teams, Prediction model fillable/cast/relationship, TournamentMatch knockout helper methods, PredictionFactory update, and focused tests. No UI, scoring, settlement or controller changes were included.

### Suggested commit message
Add qualified team prediction data model

### Ticket ID
E10-T02B

### Title
Add knockout qualified-team prediction flow and scoring

### Status
Done

### Sprint
Sprint 6

### Priority
High

### Objective
Allow users to predict the qualified team for knockout matches and score knockout predictions using the defined 6/3/0 rules.

### Scope
- Update inline predictions page to show qualified-team selection for knockout matches.
- Update single-match prediction fallback view if still available.
- Validate predicted_qualified_team_id for knockout matches.
- The selected qualified team must be either team_a_id or team_b_id.
- Persist predicted_qualified_team_id.
- Update scoring logic:
  - knockout exact score + correct qualified team = 6
  - knockout correct qualified team without exact score = 3
  - knockout incorrect qualified team = 0
  - non-knockout scoring remains unchanged
- Use matches.winner_team_id as actual qualified team for knockout scoring.
- Update prediction history to show predicted qualified team if relevant.
- Add focused tests for knockout scoring and validation.

### Note
Implemented with qualified-team selection in inline and single-match prediction flows, validation that the selected qualified team belongs to the match, persistence of predicted_qualified_team_id, knockout scoring logic using winner_team_id, history display of predicted qualified team, and focused tests for knockout scoring and prediction flow.

### Out of scope
- No bracket visualization.
- No automatic advancement.
- No penalty shootout score details.
- No API integration.
- No leaderboard redesign.
- No private league changes.
- No admin CRUD.

### Acceptance criteria
- php artisan test passes.
- php artisan migrate:fresh --seed passes.
- npm run build passes.
- Knockout matches require qualified team prediction.
- Selected qualified team must belong to the match.
- Group-stage predictions continue working without qualified team.
- Exact score + correct qualified team gives 6.
- Correct qualified team without exact score gives 3.
- Incorrect qualified team gives 0.
- Placeholder knockout matches remain blocked until teams are assigned.
- No out-of-scope features are implemented.

### Suggested commit message
Add knockout qualified team predictions

## EPIC 11 - UX / UI

### Ticket ID
E11-T00

### Title
Define Prode design system tokens

### Status
Done

### Sprint
Sprint 7

### Priority
High

### Objective
Define the visual design system foundation for Prode using the provided design tokens, so future UI work is consistent and aligned with the intended look and feel.

### Note
A design-system documentation scaffold exists in docs/design-system.md, and docs/ui-guidelines.md / docs/codex-rules.md were updated to reference it. Actual visual tokens such as typography, colors, spacing, radius and shadows are still pending decision.

Future UI polish should not assume final visual values until actual design tokens are provided.

### Scope
- Document the design tokens in docs/ui-guidelines.md or a dedicated docs/design-system.md.
- Define typography rules.
- Define color palette.
- Define spacing and layout rules.
- Define border radius and shadow rules.
- Define button styles.
- Define input styles.
- Define card styles.
- Define badges/status styles.
- Define mobile-first layout principles.
- Update docs/codex-rules.md so Codex must read the design system before UI work.
- Update Tailwind configuration only if needed and only with approved tokens.
- Do not redesign screens yet.

### Out of scope
- No full UI redesign.
- No screen-by-screen polish.
- No new product features.
- No logic changes.
- No route changes.
- No database changes.
- No external packages unless explicitly required.
- No copyrighted font files committed to the repository.

### Acceptance criteria
- Design tokens are documented.
- Typography rules are documented.
- Colors and semantic usage are documented.
- Tailwind config reflects the approved tokens if appropriate.
- Codex rules require reading the design system before UI work.
- Existing screens are not redesigned in this ticket.
- No out-of-scope features are implemented.

### Suggested commit message
Define Prode design system tokens

### Ticket ID
E11-T00B

### Title
Extract Prode visual system from prediction mock

### Status
Todo

### Sprint
Sprint 7

### Priority
High

### Objective
Translate the Prode prediction mock into a practical visual system for implementation, so future UI polish is based on the actual intended look and feel instead of generic Tailwind/Breeze styling.

### Context
The visual reference is the Prode prediction mock uploaded as project source. It shows the intended direction for the primary predictions screen.

The product owner confirmed:
- The mock is the visual starting point.
- The first implementation target will be the predictions screen.
- Preferred public font candidates are Inter or Plus Jakarta Sans.
- The exact proprietary font may be replaced later if legally available.
- Do not commit proprietary font files.

### Scope
- Update docs/design-system.md with a concrete visual interpretation of the Prode prediction mock.
- Update docs/ui-guidelines.md if needed to reference the mock-derived visual system.
- Update docs/codex-rules.md if needed so future UI work must read docs/design-system.md before editing views.
- Define the visual direction for:
  - app shell
  - top header
  - page header
  - bottom navigation
  - date/filter pills
  - match cards
  - score inputs
  - status badges
  - floating save button
  - empty states
  - success/error states
  - team flag/code display
  - knockout placeholder display
  - qualified-team selector
- Document font choice:
  - Primary recommendation: Plus Jakarta Sans or Inter.
  - Use public web font only if safely available.
  - Do not commit proprietary fonts.
  - Score/numeric UI should use the same family with strong weight unless a better public numeric font is later selected.
- Document the mock's visual principles:
  - light premium background
  - blue/navy brand structure
  - white rounded cards
  - soft shadows
  - strong blue active states
  - green open state
  - orange urgent state
  - light blue placeholder state
  - gray finished/disabled state
  - circular flags or country-code fallback
  - large readable score controls
  - clear mobile-first hierarchy
- Document what remains pending:
  - exact HEX colors if not explicitly defined
  - final official font if not legally available
  - exact shadow/radius values if not extracted yet
- Do not redesign app views in this ticket.
- Do not modify routes, controllers, models, migrations, seeders, tests or business logic.
- Do not install packages.

### Out of scope
- No screen implementation.
- No Tailwind theme changes unless strictly necessary for documentation references.
- No UI redesign.
- No app logic changes.
- No database changes.
- No new features.
- No external packages.
- No copyrighted/proprietary font files committed.

### Acceptance criteria
- docs/design-system.md reflects the Prode prediction mock as the visual source of truth.
- The document includes specific guidance for the predictions screen components.
- Font recommendation is documented as Inter or Plus Jakarta Sans, with proprietary font warning.
- Future UI work has clear rules to avoid generic Breeze/Tailwind styling.
- Existing screens are not modified.
- No out-of-scope features are implemented.

### Suggested commit message
Document Prode visual system from prediction mock

### Ticket ID
E11-T01A-1

### Title
Redesign predictions screen from Prode mock

### Status
Todo

### Sprint
Sprint 7

### Priority
High

### Objective
Redesign the primary /predictions screen using the Prode prediction mock and docs/design-system.md, improving visual quality, mobile usability, hierarchy and prediction clarity without changing business logic.

### Scope
- Redesign only the /predictions screen and any small shared layout/nav pieces strictly required for that screen.
- Use the Prode prediction mock as the visual reference.
- Follow docs/design-system.md.
- Use Plus Jakarta Sans or Inter as the public font direction if safe and already possible.
- Improve:
  - top section/header for predictions
  - date grouping
  - filter/date pills if feasible without changing backend logic
  - status legend
  - match cards
  - score inputs
  - knockout qualified-team selector
  - placeholder match presentation
  - closed/finished states
  - floating save button
  - success/error feedback
  - mobile spacing and hierarchy
- Keep Blade + Tailwind + vanilla JavaScript.
- Keep copy in Spanish.
- Avoid gambling/betting language.
- Preserve existing prediction behavior and validation.

### Out of scope
- No business logic changes.
- No database changes.
- No route changes unless absolutely necessary.
- No redesign of dashboard, history, leaderboard, private leagues or admin screens.
- No full brand palette finalization beyond the documented mock-derived style.
- No proprietary font files.
- No external packages.
- No React, Vue or Inertia.

### Acceptance criteria
- php artisan test passes.
- php artisan migrate:fresh --seed passes.
- npm run build passes.
- /predictions visually follows the Prode prediction mock direction.
- Users can still save bulk predictions.
- Existing predictions still prefill.
- Knockout qualified-team selector still works.
- Placeholder/closed/finished states still behave correctly.
- Floating save button still appears when inputs change.
- No out-of-scope features are implemented.

### Suggested commit message
Redesign predictions screen from Prode mock

### Ticket ID
E11-T01B-1

### Title
Create unified leagues hub and simplify private league detail UX

### Status
Done

### Sprint
Sprint 7

### Priority
High

### Objective
Create a unified leagues hub where users can switch between the General League and their active private leagues, and simplify private league detail so ranking is primary while owner management is collapsed.

### Note
Implementation was already present and verified. /leagues exists as leagues.index, Ligas is the primary navigation destination, General and up to 5 active private leagues are shown in a switcher, /leaderboard remains available, and private league detail prioritizes ranking with owner management collapsed under Gestionar liga.

### Scope
- Add a unified authenticated leagues hub at /leagues.
- Show General and up to 5 active private leagues in a mobile-friendly switcher.
- Keep /leaderboard available for compatibility.
- Make Ligas the primary navigation destination for ranking and leagues.
- Prioritize private league ranking and collapse owner-only management content.

### Out of scope
- No scoring changes.
- No prediction flow changes.
- No admin flow changes.
- No database schema changes.
- No separate settings route.

### Acceptance criteria
- Authenticated user can access /leagues.
- Guest is redirected from /leagues.
- /leagues defaults to General ranking.
- /leagues shows up to 5 active private league options for the user.
- Private league switcher options show private league rankings.
- User with no private leagues sees useful create/search actions.
- Navigation exposes Ligas as the primary ranking/leagues destination.
- /leaderboard still works.
- Private league detail prioritizes ranking.
- Owner management information is collapsed or hidden behind a clear section/menu.
- Owner can still invite, accept/reject requests, remove members and view recent audit activity.
- Members still see league ranking.
- Non-members/removed access rules remain enforced.

### Suggested commit message
Create unified leagues hub

### Ticket ID
E11-T02A

### Title
Team-focused calendar view

### Status
Done

### Sprint
Sprint 7

### Priority
High

### Objective
Make the calendar useful and non-redundant by turning it into a team-focused schedule screen, while removing "Partidos" from the main navigation.

### Note
Implemented /calendar as a team-focused schedule view using /calendar?team_id={id}. It lists only assigned matches for the selected team, handles no selection, invalid team and no known matches with clear empty states, shows opponent, group/stage, status, local date/time enhancement and finished results. /matches remains available directly, but Partidos was removed as a primary navigation item. Added CalendarTeamScheduleTest. Full php artisan test passes with 120 tests and 378 assertions.

### Scope
- Remove "Partidos" from primary navigation.
- Keep /matches route working for compatibility, but do not expose it as a primary nav item.
- Update /calendar to work as a team schedule view.
- Add a team selector at the top of /calendar.
- Allow selecting a team and showing only matches where that team is team_a or team_b.
- Use a query parameter such as /calendar?team_id={id} or equivalent simple approach.
- If simple, remember the last selected team in localStorage.
- Show selected team matches as cards visually aligned with the redesigned /predictions screen.
- Show:
  - selected team
  - opponent
  - date
  - local time
  - group/stage
  - status
  - result if finished
- If no team is selected, show a helpful empty state inviting the user to choose a team.
- If the selected team has no known future matches, show a clear empty state.
- For knockout stages, show matches only once the selected team is assigned to the match.
- Keep Spanish copy.
- Avoid gambling/betting language.
- Follow docs/design-system.md.

### Out of scope
- No prediction changes.
- No scoring changes.
- No bracket simulation.
- No hypothetical knockout paths.
- No automatic advancement.
- No API integration.
- No favorite_team_id database field unless explicitly decided later.
- No external packages.
- No React, Vue or Inertia.

### Acceptance criteria
- php artisan test passes.
- php artisan migrate:fresh --seed passes.
- npm run build passes.
- "Partidos" no longer appears as a primary navigation item.
- /matches still works if visited directly.
- /calendar lets the user select a team.
- /calendar shows only matches for the selected team.
- Finished matches show result.
- Upcoming matches show local date/time.
- No-team-selected state is clear.
- Knockout future matches appear only after teams are assigned.
- No out-of-scope features are implemented.

### Suggested commit message
Rework calendar into team schedule view

### Ticket ID
E11-T04A

### Title
Redirect root and improve dashboard UX

### Status
Done

### Sprint
Sprint 8

### Priority
High

### Objective
Improve the first-entry experience by redirecting `/` to the right authenticated/guest destination and turning Inicio into a useful user summary.

### Note
Implemented `/` redirects for guests and authenticated users, replaced the generic dashboard button hub with a Spanish-first user home showing Liga general points/position, pending prediction count, scored prediction count, active private league summary, primary Predicciones CTA, Ligas/Historial/Calendario links, and an admin-only shortcut. Added feature coverage for root redirects, dashboard labels, and admin shortcut visibility.

### Scope
- Redirect guests visiting `/` to `/login`.
- Redirect authenticated users visiting `/` to `/dashboard`.
- Replace the generic dashboard hub with a useful user summary.
- Show Liga general points and position when available.
- Show pending/open prediction information when simple.
- Keep Ligas as the primary league/ranking destination.
- Keep admin shortcut visible only for admins.

### Out of scope
- No business rule changes.
- No scoring changes.
- No database schema changes.
- No marketing homepage.
- No route removals.
- No external packages.

### Acceptance criteria
- Guest users visiting `/` are redirected to login.
- Authenticated users visiting `/` are redirected to dashboard.
- Dashboard shows useful Spanish-first summary content and primary CTAs.
- Admin shortcut remains admin-only.
- Existing tests and E2E smoke suite continue passing.

### Suggested commit message
Improve root redirect and dashboard UX

### Ticket ID
E11-T04B

### Title
Polish ranking table UI

### Status
Done

### Sprint
Sprint 8

### Priority
Medium

### Objective
Make ranking and table-of-positions views more compact, readable, and mobile-friendly without changing ranking logic.

### Note
Implemented as a visual-only Blade/Tailwind polish using a reusable `ranking-table` component for `/leagues`, private league detail rankings, and `/leaderboard`. Rankings now render as compact standings-style rows with short mobile labels, full desktop labels, stronger points column, first-place highlight, and subtle authenticated-user highlight. No queries, scoring, sorting, routes, or membership rules were changed.

### Scope
- Compact ranking rows for Liga general and private league rankings.
- Apply the same visual treatment to `/leagues`, private league detail, and `/leaderboard`.
- Show position, user, points, exact results, trends, and scored prediction counts.
- Highlight first place and the authenticated user's row subtly.
- Keep Spanish terminology and mobile-first layout.

### Out of scope
- No ranking logic changes.
- No scoring changes.
- No new metrics.
- No filters, charts, pagination, API, admin, or database changes.

### Acceptance criteria
- Ranking views are more compact on mobile.
- Rankings still show the same users and ordering.
- `/leagues` still shows Liga general and private league rankings.
- `/leaderboard` still works.
- Private league rankings still exclude removed users.
- Existing tests and E2E smoke suite continue passing.

### Suggested commit message
Polish ranking table UI

### Ticket ID
E11-T04C

### Title
Prepare Google login with Laravel Socialite

### Status
Done

### Sprint
Sprint 8

### Priority
Medium

### Objective
Add Google OAuth login as a low-friction authentication option while keeping traditional email/password login intact.

### Note
Implemented with Laravel Socialite for Google only. Added Google OAuth service configuration, nullable Google auth fields on users, Google redirect/callback routes, a small `GoogleAuthController`, deterministic username generation with numeric collision suffixes, and a Prode-styled `Continuar con Google` CTA that is hidden unless Google credentials are configured. Existing users are linked by email without duplication, new Google users receive secure random passwords to preserve the existing non-null password schema, and email/password login remains unchanged. Added focused feature tests with mocked Socialite responses and no real Google calls.

### Scope
- Add `laravel/socialite`.
- Configure Google OAuth through environment variables.
- Add nullable user fields for `google_id`, `avatar_url`, and `auth_provider`.
- Add Google auth redirect and callback routes.
- Create or link users from Google callback without duplicating existing email users.
- Add Google CTA to login/register only when configured.
- Update staging QA documentation.

### Out of scope
- No additional OAuth providers.
- No hardcoded Google credentials.
- No removal of email/password login.
- No changes to scoring, predictions, leagues, admin flows, demo reset commands, or routes outside Google auth additions.

### Acceptance criteria
- Email/password login and registration continue working.
- Google routes exist and handle missing config gracefully.
- Google callback creates new users and links existing email users without duplication.
- Generated usernames handle collisions deterministically.
- Google button appears only when credentials are configured.
- Tests mock Socialite and do not call Google.

### Suggested commit message
Prepare Google login

### Ticket ID
AUTH-T02

### Title
Google OAuth auto-registration

### Status
Done

### Sprint
Sprint 8

### Priority
High

### Objective
Make `Continuar con Google` work as a true login-or-register flow for verified Google accounts.

### Note
Google OAuth now links existing users by Google ID or email, populates Google profile fields, verifies matching traditional accounts when Google confirms the email, and creates new verified users automatically when no app account exists. New Google-created users skip the Brevo email-code verification flow. If Google does not return an email, or explicitly reports the email as unverified, the callback fails gracefully back to login with a Spanish validation error.

### Scope
- Create new users from verified Google profiles.
- Generate unique usernames from the Google display name, falling back to the email local part.
- Preserve traditional email/password registration and verification behavior.
- Link existing users and populate `google_id`, `avatar_url`, and `auth_provider`.
- Add feature tests using mocked Socialite responses with no real Google calls.

### Out of scope
- No scoring, predictions, leagues, API-Football sync, Brevo delivery, Railway, or production config changes.
- OAuth-specific registration rate limiting remains a future hardening option.

### Acceptance criteria
- Existing Google users can log in.
- New Google users are created, verified, authenticated, and redirected to dashboard.
- Google-created users do not receive verification-code emails.
- Missing or explicitly unverified Google emails are rejected gracefully.

### Suggested commit message
Allow Google OAuth auto-registration

### Ticket ID
AUTH-T03

### Title
Admin manual email verification override

### Status
Done

### Sprint
Sprint 8

### Priority
High

### Objective
Give admins a safe fallback to mark a user's email as verified when the normal Brevo/email-code flow fails or the provider limit is reached.

### Note
Implemented an admin users page at `/admin/users` and a CSRF-protected manual verification action at `/admin/users/{user}/verify-email`. The action only sets `email_verified_at` when it is null, is idempotent for already verified users, and does not change passwords, roles, Google fields, predictions, leagues, or any other user data. This is not an account approval system and does not add pending/approved/rejected account states.

### Scope
- Show users with name, username, email, role, email verification status/date, created date, and Google linked status.
- Allow admins to manually verify unverified emails.
- Keep the existing Brevo/email-code verification flow unchanged.
- Keep Google-created users verified automatically.
- Add feature coverage for admin access, non-admin denial, guest redirects, verification action, idempotent already-verified behavior, and verified-gate access after override.

### Out of scope
- No approval status, user approval workflow, rejection flow, unverify action, scoring, predictions, leagues, API-Football sync, timezone, auth provider, or Railway config changes.

### Acceptance criteria
- Admins can see users and email verification status.
- Admins can manually mark an email as verified.
- Manually verified users pass the existing email verification gate.
- Guests and non-admins cannot access the users page or verification action.

### Suggested commit message
Add admin email verification override

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
Done

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

Note: Implemented using a dedicated MySQL testing database instead of SQLite. The full php artisan test suite now runs without SQLite driver errors, and local test setup is documented without committing real secrets.

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
E12-T01A

### Title
Core v1 prediction/auth coverage

### Status
Done

### Sprint
Sprint 8

### Priority
High

### Objective
Add focused feature test coverage for the core v1 prediction/auth flow.

### Note
Implemented with CorePredictionFlowTest covering authenticated/guest prediction access, inline bulk save and prefill, single-match fallback save, deadline enforcement, locked/finished/placeholder blocking, prediction history pending/scored states, group-stage behavior without qualified team, and knockout qualified-team validation/persistence. Full php artisan test passes with 69 tests and 160 assertions.

### Scope
- Cover authenticated and guest access to prediction routes.
- Cover inline prediction bulk save and prefill.
- Cover single-match prediction fallback.
- Cover prediction deadline and non-predictable match blocking.
- Cover prediction history pending and scored states.
- Cover group-stage and knockout qualified-team behavior.

### Out of scope
- No private league tests.
- No UI redesign.
- No product feature changes.
- No deployment work.

### Acceptance criteria
- php artisan test passes.
- Core prediction/auth flows have meaningful feature coverage.
- Existing tests continue to pass.
- No out-of-scope features are implemented.

### Suggested commit message
Add core prediction feature coverage

### Ticket ID
E12-T01B

### Title
Private leagues and leaderboard coverage

### Status
Done

### Sprint
Sprint 8

### Priority
High

### Objective
Add focused feature test coverage for private league and private league leaderboard workflows.

### Note
Implemented with PrivateLeagueFlowTest and PrivateLeagueLeaderboardTest covering league creation, one-owned-league limit, duplicate names, unique codes, owner auto-membership, guest access, search by name/code, join requests, duplicate blocking, own-league blocking, active-member blocking, accept/reject, non-owner blocking, max 5 active leagues, owner/member/non-member access, invitation states, member removal, audit log and removed-member leaderboard exclusion.

### Scope
- Cover private league creation and ownership limits.
- Cover league search by name and visible code.
- Cover join requests, approval/rejection, and membership limits.
- Cover invitation link states.
- Cover member removal and audit logging.
- Cover private league leaderboard ordering and removed-member exclusion.

### Out of scope
- No new product features.
- No UI redesign.
- No admin tests.
- No deployment work.

### Acceptance criteria
- php artisan test passes.
- Private league workflows have meaningful feature coverage.
- Existing tests continue to pass.
- No out-of-scope features are implemented.

### Suggested commit message
Add private league feature coverage

### Ticket ID
E12-T01C

### Title
Admin/result/settlement coverage

### Status
Done

### Sprint
Sprint 8

### Priority
High

### Objective
Add focused feature test coverage for admin, result loading/correction, settlement and leaderboard integration.

### Note
Implemented with AdminDashboardTest and AdminResultSettlementTest covering admin access, dashboard environment/mode/counts, admin matches listing, manual result save/correction, winner/draw logic, idempotent rescoring, settlement points/status, placeholder blocking, placeholder team assignment, and leaderboard updates after settlement. Full php artisan test passes with 112 tests and 344 assertions.

### Scope
- Cover admin dashboard access, environment/mode display, counts and links.
- Cover admin matches listing access and match display.
- Cover manual result save/correction and winner/draw logic.
- Cover idempotent prediction settlement and rescoring.
- Cover placeholder result blocking and placeholder team assignment.
- Cover global and private league leaderboard updates after settlement.

### Out of scope
- No new product features.
- No UI redesign.
- No private league feature changes.
- No scoring rule changes.
- No deployment work.

### Acceptance criteria
- php artisan test passes.
- Admin/result/settlement flows have meaningful feature coverage.
- Existing tests continue to pass.
- No out-of-scope features are implemented.

### Suggested commit message
Add admin settlement feature coverage

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
Done

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

### Note
Implemented with targeted validation and authorization hardening for predictions, private leagues, removed members, invite access, admin result access and placeholder/invalid score handling. Added HardenValidationAuthorizationTest.php and verified phpunit, migrate:fresh --seed --force and npm run build.

### Suggested commit message
fix: harden validation and authorization

## EPIC 13 - API de fixtures y resultados

### Ticket ID
TICKET-API-001

### Title
Integrar API de fixtures y resultados

### Estado
TODO

### Sprint
Future

### Prioridad
Medium

### Objetivo
Integrar una fuente externa para obtener fixtures y resultados reales del torneo, reduciendo la dependencia de carga manual.

### Alcance
- Evaluar proveedor de API de fixtures/resultados.
- Definir configuración de credenciales y entorno.
- Diseñar el flujo de importación/sincronización.
- Mapear datos externos a TournamentMatch y Team.
- Registrar errores o inconsistencias para revisión admin.

### Fuera de alcance
- No implementar scoring automático en este ticket.
- No reemplazar todavía el fallback manual.
- No crear UI admin avanzada.
- No agregar proveedor específico sin decisión previa.

### Criterios de aceptación
- La API elegida y el enfoque de integración quedan definidos.
- El sistema puede obtener o preparar fixtures/resultados desde la fuente externa.
- La carga manual sigue disponible como fallback.
- No se implementan funcionalidades fuera de alcance.

### Commit sugerido
Integrate fixtures and results API

### Ticket ID
TICKET-API-002

### Title
Sincronizar resultados reales automáticamente

### Estado
TODO

### Sprint
Future

### Prioridad
Medium

### Objetivo
Sincronizar resultados reales automáticamente desde la API externa para actualizar partidos finalizados.

### Alcance
- Crear proceso de sincronización automática o comando programable.
- Actualizar resultados reales cuando la API confirme un partido finalizado.
- Manejar demoras, errores y datos inconsistentes.
- Mantener posibilidad de corrección manual por admin.
- Preparar integración futura con scoring/recalculo.

### Fuera de alcance
- No implementar leaderboard.
- No implementar rankings por liga.
- No eliminar la corrección manual.
- No asumir exactitud absoluta de la API sin validaciones.

### Criterios de aceptación
- Resultados finalizados pueden sincronizarse desde la API.
- Fallas o demoras quedan visibles o registradas para revisión.
- Admin puede corregir manualmente si la API falla o se demora.
- No se implementan funcionalidades fuera de alcance.

### Commit sugerido
Sync real match results automatically

## EPIC 14 - Deploy / Railway

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

### Ticket ID
E14-T02A

### Title
Document staging QA strategy

### Status
Done

### Sprint
Sprint 8

### Priority
High

### Objective
Create a maintainable staging QA strategy for validating the product on Railway staging before production.

### Scope
- Create `docs/staging-qa.md`.
- Document staging/local/production environment separation.
- Document safety rules for demo data and destructive commands.
- Define future demo data, result simulation, reset/seed flow, and Playwright QA strategy.
- Add follow-up QA tickets for implementation.

### Out of scope
- No application code.
- No seeders.
- No Artisan commands.
- No Playwright installation.
- No migrations, models, controllers, routes, or views.

### Acceptance criteria
- `docs/staging-qa.md` exists.
- Staging QA phases, safety rules, future commands, and manual smoke checklist are documented.
- Follow-up QA tickets are listed in the backlog.
- Future feature work has a QA maintenance rule.

### Suggested commit message
Document staging QA strategy

### Ticket ID
E14-T02B

### Title
Add staging demo seed and safe reset command

### Status
Done

### Sprint
Sprint 8

### Priority
High

### Objective
Create deterministic staging demo data and a safe reset flow for Railway staging QA.

### Note
Implemented with `Database\Seeders\StagingDemoSeeder` and `php artisan demo:reset-staging`. The command blocks production/live environments, runs `migrate:fresh --seed --force`, then seeds deterministic QA data for demo users, teams, matches, private league flows, pending predictions, placeholders, an assigned knockout match, and scored finished matches.

### Scope
- Add a staging demo seeder or equivalent deterministic demo data flow.
- Include demo users, tournament data, matches, predictions, private leagues, memberships, and useful join requests.
- Add a safe reset command or documented command flow for staging.
- Block destructive reset behavior outside local, testing, and staging.

### Out of scope
- No Playwright tests.
- No production data changes.
- No external API integration.

### Acceptance criteria
- Staging can be reset to a known QA state.
- Demo data supports pre-results and post-results QA scenarios.
- Destructive commands cannot run in production/live.

### Suggested commit message
Add staging demo data reset

### Ticket ID
E14-T02C

### Title
Add demo result simulation command

### Status
Done

### Sprint
Sprint 8

### Priority
High

### Objective
Simulate API-like match result arrival for staging QA.

### Note
Implemented with `php artisan demo:simulate-results --scenario=group-day-1`. The command blocks production/live environments, applies deterministic QA results to known staging demo matches, marks them finished, sets `winner_team_id`, and reuses `MatchPredictionSettlementService` so points are recalculated idempotently.

### Scope
- Add a command such as `php artisan demo:simulate-results --scenario=group-day-1`.
- Apply result data to selected matches.
- Set `winner_team_id`.
- Mark matches finished.
- Reuse existing settlement/scoring logic.
- Keep repeated execution safe where possible.

### Out of scope
- No external API provider.
- No scoring rule changes.
- No Playwright tests.

### Acceptance criteria
- Simulated results update matches and prediction points.
- Rankings and history reflect simulated results.
- Command is blocked in production/live.

### Suggested commit message
Add demo result simulation

### Ticket ID
E14-T02D

### Title
Add Playwright staging QA smoke suite

### Status
Done

### Sprint
Sprint 8

### Priority
High

### Objective
Add a Playwright smoke suite that can run against Railway staging with a configurable `BASE_URL`.

### Note
Implemented with `@playwright/test`, `playwright.config.js`, npm scripts for headless/headed/report runs, and focused Chromium smoke tests under `tests/e2e` covering auth, predictions, leagues, history, and admin. The suite uses `PLAYWRIGHT_BASE_URL` with local fallback, demo credentials, screenshots on failure, retained traces on failure, and an HTML report.

### Scope
- Add Playwright setup.
- Cover auth smoke, prediction pre-results flow, private league flow, admin result flow, post-results ranking/history flow, and navigation/mobile smoke.
- Use Chromium first.
- Capture screenshots/traces on failure.
- Generate an HTML report.

### Out of scope
- No production execution by default.
- No exhaustive browser matrix.
- No load testing.

### Acceptance criteria
- Playwright smoke suite runs against staging.
- Tests use stable staging demo users/data.
- Report artifacts are generated.

### Suggested commit message
Add staging Playwright smoke tests

### Ticket ID
E14-T02E

### Title
Run staging QA and produce report

### Status
Todo

### Sprint
Sprint 8

### Priority
Medium

### Objective
Run the staging QA process and produce a concise report for release readiness.

### Scope
- Reset staging demo data.
- Run pre-results checks.
- Simulate results.
- Run post-results checks.
- Run Playwright smoke suite.
- Record failures, screenshots/traces, and release risks.

### Out of scope
- No new feature implementation.
- No production deployment.

### Acceptance criteria
- Staging QA report is produced.
- Failures and release blockers are documented.
- Follow-up tickets are created for unresolved issues.

### Suggested commit message
Run staging QA report

## EPIC 15 - Team identity and local assets

### Ticket ID
E15-T03

### Title
Add local flag assets and team flag mapping

### Status
Done

### Sprint
Sprint 8

### Priority
High

### Objective
Add a local flag asset strategy and maintainable team-code mapping so teams can display stable local national flags without depending on API-Football logo URLs.

### Note
Implemented with local SVG assets in `public/flags/`, `config/team-flags.php`, `App\Support\TeamFlagMapping`, `Team` display helpers, and `php artisan teams:apply-flag-mapping`. API-Football team sync now applies mapped `country_code` and `flag_path` only when those local fields are null, while preserving existing manual values and `logo_url`.

### Scope
- Add local SVG flag assets under `public/flags/`.
- Add mapping for current seeded teams and the 2022 API-tested World Cup teams.
- Support API-Football edge cases including `COS`/`CRC`, `ENG`, `WAL`, `KOR`, and `KSA`.
- Add `teams:apply-flag-mapping` with `--dry-run`, `--force`, and `--force-update`.
- Add model/helper methods for flag URL, flag presence, and display code fallback.
- Integrate safe null-only mapping into API team sync.
- Update team identity and API-Football documentation.
- Add tests for mapping, command behavior, preservation, and API sync integration.

### Out of scope
- No UI flag display changes beyond helper methods.
- No prediction, scoring, league, admin, route, or fixture-sync behavior changes.
- No external APIs, packages, snapshots, or protected tournament branding.
- No image binaries in the database.

### Acceptance criteria
- `public/flags` contains local SVG flag assets for mapped teams.
- `teams:apply-flag-mapping` exists and works.
- Existing synced teams can receive `country_code` and `flag_path`.
- Existing manual `country_code` and `flag_path` are preserved by default.
- Tests cover mapping and command behavior.

### Suggested commit message
Add local team flag mapping

### Ticket ID
E15-T03B

### Title
Complete 2026 team flag mappings

### Status
Done

### Sprint
Sprint 8

### Priority
High

### Objective
Add local flag assets and mappings for the 22 API-Football 2026 teams that were still missing `flag_path`.

### Note
Implemented with SVG assets under `public/flags/` and `config/team-flags.php` mappings keyed by `teams.short_name`. The added mappings cover `ALG`, `AUT`, `BIH`, `CPV`, `COL`, `CGO`, `CUR`, `CZE`, `EGY`, `HAI`, `IRQ`, `CIV`, `JOR`, `NZL`, `NOR`, `PAN`, `PAR`, `SCO`, `RSA`, `SWE`, `TUR`, and `UZB`, using each code as the local `country_code` flag code.

### Scope
- Add missing local SVG flag assets.
- Map 2026 short-name codes to local `flag_path` values.
- Keep mapping based on `teams.short_name`; no `code` column is required.
- Add tests that each mapping points to an existing asset.
- Add command coverage proving `teams:apply-flag-mapping` applies the new codes.
- Update team identity documentation.

### Out of scope
- No scoring, prediction, league, auth/email, or API-Football sync changes.
- No external packages or external API calls.
- No FIFA or tournament branding.

### Acceptance criteria
- `teams:apply-flag-mapping --force` can apply all newly mapped codes.
- No mapped flag asset path is broken.
- 2026 team flag coverage is documented.

### Suggested commit message
Complete 2026 team flag mappings

### Ticket ID
E15-T04

### Title
Show flags in match cards and team UI

### Status
Done

### Sprint
Sprint 8

### Priority
Medium

### Objective
Use local team flags in match cards, predictions, calendar, rankings, and team-facing UI where they improve scan speed.

### Note
Implemented with a reusable `x-team-flag` Blade component that renders local `flag_path` assets via `asset()`, falls back to `short_name`/`country_code`/initials, and handles null placeholder teams without broken images. The component is used in prediction match cards, single prediction view, prediction history, calendar schedule cards, matches listing, and useful admin match/result views. Existing controllers already eager-load team relations, so no new N+1 query path was introduced.

### Scope
- Add reusable team flag rendering component.
- Show local flags next to team names in match-heavy user views.
- Preserve team names as primary readable text.
- Add clean fallback rendering for missing flags and placeholder teams.
- Keep `logo_url` out of primary flag rendering.
- Add component render tests.
- Update team identity documentation.

### Out of scope
- No scoring, prediction, league, admin business, auth/email, or API sync behavior changes.
- No external packages or API calls.
- No FIFA or tournament branding.

### Acceptance criteria
- Flags appear in prediction match cards when `flag_path` exists.
- Missing flags and placeholder teams render compact fallback badges.
- Existing feature tests continue to pass.

### Suggested commit message
Show local team flags

## EPIC 16 - API-Football integration

### Ticket ID
E16-T00

### Title
API-Football World Cup 2026 discovery command

### Status
Done

### Sprint
Sprint 8

### Priority
High

### Objective
Add a safe, read-only discovery command for API-Football World Cup 2026 data using league `1` and season `2026`.

### Note
Implemented with `php artisan api-football:discover-world-cup`, Laravel HTTP client configuration, conservative endpoint selection, league/season overrides, confirmation/production safety checks, optional raw JSON snapshots under private storage, API HTTP and logical error handling, useful terminal summaries, and HTTP-faked feature tests. The command does not mutate teams, matches, predictions, scores, rankings, leagues, or users.

### Scope
- Configure API-Football environment variables.
- Support discovery endpoints for teams, fixtures, rounds, standings, or all.
- Support `--league` and `--season` overrides for free-plan structure testing.
- Treat non-empty top-level API-Football `errors` as endpoint failures even when HTTP status is 200.
- Limit `--endpoint=all` to at most 4 API requests.
- Save raw snapshots only when `--save` is passed.
- Ignore saved snapshots from Git.
- Document command usage and API request budget warnings.
- Add tests with `Http::fake()`.

### Out of scope
- No database sync.
- No API mapping fields yet.
- No prediction, scoring, league, or admin behavior changes.
- No real API calls in tests.
- No external packages.

### Acceptance criteria
- Command exists and fails safely when configuration is missing.
- Endpoint requests use the API-Sports key header.
- `--endpoint=all` makes the expected 4 fakeable calls.
- `--save` writes snapshots to ignored private storage.
- API logical errors exit non-zero and can still save raw error snapshots.
- Command does not modify app data.
- Documentation explains usage and future sync path.

### Suggested commit message
Add API-Football discovery command

### Ticket ID
E16-T01

### Title
Add API mapping fields

### Status
Done

### Sprint
Sprint 8

### Priority
High

### Objective
Add database fields needed to map local teams, tournaments, phases, and matches to API-Football records.

### Scope
- Add API mapping fields to `teams` table:
  - `api_provider` (nullable string)
  - `api_team_id` (nullable unsigned big integer)
  - `country` (nullable string)
  - `logo_url` (nullable string)
  - `last_synced_at` (nullable timestamp)
  - Unique constraint: `['api_provider', 'api_team_id']`
- Add API mapping fields to `matches` table:
  - `api_provider` (nullable string)
  - `api_fixture_id` (nullable unsigned big integer)
  - `api_status` (nullable string)
  - `round` (nullable string)
  - `venue_name` (nullable string)
  - `venue_city` (nullable string)
  - `last_synced_at` (nullable timestamp)
  - Unique constraint: `['api_provider', 'api_fixture_id']`
- Update Team model fillable and casts.
- Update TournamentMatch model fillable and casts.
- Add comprehensive tests for all new fields.
- Update `docs/api-football.md` with API response mapping and field documentation.
- Create `docs/team-identity.md` for flag and logo strategy.

### Out of scope
- No database sync.
- No API calls.
- No prediction, scoring, league, or admin behavior changes.
- No image downloads or binary storage.

### Acceptance criteria
- Migration adds all required fields with correct types and constraints.
- Team and TournamentMatch models include new fields in fillable/casts.
- Tests confirm fields can be stored and are nullable.
- Unique constraints prevent duplicate API mappings.
- Existing seeders and tests continue to pass.
- `php artisan migrate:fresh --seed` succeeds.
- `php artisan demo:reset-staging --force` succeeds.
- Documentation is updated with mapping information.

### Suggested commit message
Add API-Football mapping fields

### Ticket ID
E16-T01B

### Title
Add missing team flag mapping fields

### Status
Done

### Sprint
Sprint 8

### Priority
Low

### Objective
Ensure `teams` contains `country_code` and `flag_path` local identity fields used for flags and short codes.

### Note
These fields were present in the original team model/migration. This follow-up confirms tests and documentation cover them.

### Suggested commit message
Add team flag mapping fields

### Ticket ID
E16-T02

### Title
Sync teams from API-Football

### Status
Done

### Sprint
Sprint 8

### Priority
High

### Objective
Create a safe sync flow for teams using API-Football data and local mapping fields.

### Note
Implemented with `php artisan api-football:sync-teams`. The command supports API fetches or `--from-snapshot`, `--league`, `--season`, `--force`, and `--dry-run`; makes at most 1 API request; detects HTTP and top-level API-Football logical errors; creates, updates, links, or skips teams conservatively; preserves local `country_code` and `flag_path`; ignores venue data; and includes HTTP-faked feature coverage. It does not sync fixtures, results, predictions, rankings, leagues, admin data, or image binaries.

### Scope
- Fetch teams from API-Football `/teams`.
- Upsert local `Team` records using `api_provider` and `api_team_id`.
- Link existing unmapped teams by unique `short_name` or exact name when safe.
- Map `team.name`, `team.code`, `team.country`, `team.logo`, and `last_synced_at`.
- Support dry run and snapshot mode.
- Keep sync blocked in production/live mode unless the explicit API-Football production sync flag is enabled.
- Add tests with `Http::fake()`.

### Out of scope
- No fixture sync.
- No match updates.
- No prediction, scoring, league, or admin behavior changes.
- No image downloads or binary storage.

### Acceptance criteria
- Command exists and fails safely when configuration is missing.
- Successful fake response creates and updates teams idempotently.
- Existing local `country_code` and `flag_path` are preserved.
- Venue data is ignored.
- Tests do not call the real API.

### Suggested commit message
Sync teams from API-Football

### Ticket ID
E16-T03

### Title
Sync fixtures from API-Football

### Status
Done

### Sprint
Sprint 8

### Priority
High

### Objective
Create a safe sync flow for World Cup 2026 fixtures without disrupting prediction rules.

### Note
Implemented with `php artisan api-football:sync-fixtures`. The command supports API fetches or `--from-snapshot`, `--league`, `--season`, `--force`, and `--dry-run`; makes at most 1 API request; detects HTTP and top-level API-Football logical errors; requires teams synced first via `api_provider`/`api_team_id`; upserts `TournamentMatch` by `api_provider`/`api_fixture_id`; stores date, API status, round, venue, home/away team mapping, and safe finished scores; and includes HTTP-faked feature coverage. It does not create teams, settle predictions, call `MatchPredictionSettlementService`, sync rankings, sync leagues, or change admin behavior.

### Scope
- Fetch fixtures from API-Football `/fixtures`.
- Upsert local `TournamentMatch` records using `api_provider` and `api_fixture_id`.
- Map API home team to `team_a_id` and API away team to `team_b_id`.
- Store `fixture.date`, `fixture.status.short`, `league.round`, venue name/city, and `last_synced_at`.
- Skip fixtures whose local teams are missing, with guidance to run `api-football:sync-teams` first.
- Support dry run and snapshot mode.
- Keep sync blocked in production/live mode unless the explicit API-Football production sync flag is enabled.
- Add tests with `Http::fake()`.

### Out of scope
- No team creation.
- No prediction settlement.
- No scoring rules changes.
- No private league, ranking, route, or admin behavior changes.
- No direct real API calls in tests.

### Acceptance criteria
- Command exists and fails safely when configuration is missing.
- Successful fake response creates and updates fixtures idempotently.
- Missing teams are skipped with clear output.
- Dry run does not mutate the database.
- Finished fixtures can store scores without settling predictions.
- Tests do not call the real API.

### Suggested commit message
Sync fixtures from API-Football

### Ticket ID
OPS-T01

### Title
Allow controlled production API-Football sync

### Status
Done

### Sprint
Sprint 8

### Priority
High

### Objective
Allow the official production initial sync and cron to run API-Football commands in `APP_ENV=production` or `APP_MODE=live` only when explicitly enabled.

### Note
Implemented `API_FOOTBALL_ALLOW_PRODUCTION_SYNC=false` in service config and a shared production/live guard for `api-football:sync-teams`, `api-football:sync-fixtures`, and `api-football:discover-world-cup`. Production/live still refuses by default, and `--force` does not bypass the guard. When the flag is enabled, the commands warn clearly before continuing. Demo reset and result simulation protections are unchanged.

### Scope
- Add `services.api_football.allow_production_sync`.
- Keep production/live API sync blocked by default.
- Allow production/live API sync only when the explicit API-Football flag is true.
- Add HTTP-faked tests for refused and allowed production/live teams and fixtures sync.
- Document production cron usage and demo reset guardrails.

### Out of scope
- No scoring, predictions, leagues, auth, API mapping, Railway variable, or demo reset behavior changes.
- No real API calls in tests.

### Acceptance criteria
- Production/live sync refuses by default.
- Production/live sync runs only with `API_FOOTBALL_ALLOW_PRODUCTION_SYNC=true`.
- Staging/non-production sync behavior remains unchanged.

### Suggested commit message
Allow controlled production API sync

### Ticket ID
OPS-T02

### Title
Define production backup, release tagging and rollback procedure

### Status
Todo

### Sprint
Operational follow-up

### Priority
High

### Objective
Define a clear low-risk production release safety procedure covering code rollback, database backup/restore strategy, release tagging, risk classification, and pre/post deploy checks.

### Scope
- Document that production has auto-deploy OFF and this is an operational advantage.
- Document that staging has auto-deploy ON and must be validated before production.
- Define rollback of code as the first response for visual, layout, navigation, or non-data incidents.
- Define database backup/PITR/restore as a data-recovery layer, not the default rollback mechanism.
- Reserve database restore for real data incidents involving users, predictions, league memberships, scoring, matches, API sync, or corrupted/incorrect production data.
- Define simple production tag conventions, for example:
  - `prod-YYYY-MM-DD-pre-epic18`
  - `prod-YYYY-MM-DD-epic18`
  - `prod-YYYY-MM-DD-pre-sensitive-change`
- Define a pre-deploy checklist:
  - verify local tests/build
  - verify staging deploy/QA
  - inspect git diff/stat/name-only
  - classify risk by files touched
  - create or verify DB backup when the deploy is sensitive
  - tag the previous known-good production commit
  - confirm production web/cron DB target if the change is infrastructure-related
- Define a post-deploy checklist:
  - QA production manually/read-only
  - do not run the current Playwright suite against production
  - verify key pages and admin health
  - tag the successful production release if appropriate
- Define risk classification:
  - Low data risk: views, CSS, docs, tests, visual-only Blade changes.
  - Sensitive deploy: migrations, predictions, scoring/settlement, auth/users, league memberships, API sync/commands, config/env/Railway, DB-related code, destructive commands.
- Include read-only verification command examples or references for counts/checks.
- Document that if something looks wrong after production deploy, rollback/redeploy of code should be attempted before touching the database unless data damage is confirmed.
- Document that restore DB/PITR should be used carefully and only after deciding that production data needs recovery.

### Out of scope
- No application code changes.
- No Railway variable changes.
- No production commands.
- No real backup or restore execution.
- No migrations.
- No changes to scoring, predictions, leagues, auth, API sync, or deployment config.
- No automatic release tooling yet.

### Acceptance criteria
- `docs/backlog.md` contains a clear OPS-T02 ticket.
- The ticket distinguishes code rollback from DB restore.
- The ticket defines simple production release tags.
- The ticket defines deploy risk classification.
- The ticket defines pre/post deploy checklist expectations.
- The ticket reinforces that production deploys require staging validation first.
- The ticket makes clear that the user prefers reverting quickly over risking production data.

### Suggested commit message
docs: add production rollback procedure ticket

### Ticket ID
E16-T04

### Title
Sync results and settle predictions

### Status
Done

### Sprint
Sprint 8

### Priority
High

### Objective
Create a controlled result sync flow that updates finished matches and reuses existing prediction settlement logic.

### Suggested commit message
Sync API results and settle predictions

### Ticket ID
E16-T05

### Title
API sync logs/admin visibility

### Status
Todo

### Sprint
Future

### Priority
Medium

### Objective
Expose API sync history, errors, and last-run status for safe operational visibility.

### Note
Implemented with `api_sync_logs`, `App\Models\ApiSyncLog`, safe logging integration for API-Football discovery/team/fixture commands, and the admin-only `/admin/api-health` screen. The screen shows latest successful team and fixture syncs, latest failure, API team/fixture counts, teams missing flags, fixtures by API status, recent sync logs, and read-only command hints. Sync logs store compact metadata and counters only; they do not store API keys or full raw API responses.

### Scope
- Create `api_sync_logs` for compact sync history.
- Log success, failure, and skipped states from API-Football discovery, team sync, and fixture sync commands.
- Capture HTTP status, rate-limit headers, duration, item counters, short errors, and small metadata.
- Add admin-only API health page.
- Add `API_SYNC_HEALTH_WARNING_MINUTES`.
- Add tests for model storage, command logging, API error logging, and admin access.

### Out of scope
- No result settlement.
- No scheduler or automatic 5-minute sync.
- No scoring, prediction, league, auth/email, or sync mapping changes.
- No API keys or full API response bodies in the database.

### Acceptance criteria
- Sync commands write logs without calling real APIs in tests.
- Admin can view `/admin/api-health`.
- Non-admin users cannot view `/admin/api-health`.
- Health screen shows recent sync status and useful counters.

### Suggested commit message
Add API sync health admin panel

### Ticket ID
FIX-PRED-DATE-NAV

### Title
Fix predictions date navigation

### Status
Done

### Sprint
Sprint 8

### Priority
High

### Objective
Make `/predictions` date navigation work with real World Cup 2026 fixtures by showing only dates that have matches and rendering the selected match date.

### Note
Implemented with a distinct match-date selector based on `TournamentMatch.starts_at`, using the app timezone consistently for option generation, default date selection, and displayed match times. The page now defaults to today when today has matches, otherwise the next available match date, otherwise the first available match date. Selecting `?date=YYYY-MM-DD` filters the match list to that date and orders matches by `starts_at`.

Follow-up polish keeps the active date chip visible on mobile after page reload by centering it in the horizontal scroller on `DOMContentLoaded`. The `Cierra pronto` badge now appears only while predictions are open and the signed time remaining until `predictionClosesAt` is between 0 and 60 minutes, so matches days away stay labeled as open.

### Scope
- Build horizontal date chips from actual match dates.
- Hide empty generic calendar days.
- Filter `/predictions` matches by selected date.
- Keep existing prediction cards, flags, locking, and submit behavior.
- Center the active date chip on mobile after selecting a date.
- Limit `Cierra pronto` to less than or equal to 1 hour before prediction lock.
- Add feature tests for date options, filtering, default date, ordering, and empty state.

### Out of scope
- No scoring changes.
- No prediction locking rule changes.
- No league, API-Football sync, calendar, or admin behavior changes.

### Acceptance criteria
- `/predictions` shows only dates with matches.
- Date chips change the visible match list.
- Empty dates do not appear.
- Existing prediction submit/edit tests still pass.

### Suggested commit message
Fix predictions date navigation

### Ticket ID
TZ-01

### Title
Fix predictions local timezone display

### Status
Done

### Sprint
Sprint 8

### Priority
Critical

### Objective
Make `/predictions` display match times, edit-until times, and date chips in the viewer's local timezone, consistent with `/calendar`.

### Note
Implemented `/predictions` timezone handling around a `tz` query parameter populated from `Intl.DateTimeFormat().resolvedOptions().timeZone` when available, with `config('app.timezone')` as the server fallback. Date chips and selected-date filtering now use the viewer timezone, so `?date=YYYY-MM-DD` refers to the viewer's local match date. Match kickoff and edit-until labels also carry local-time data attributes for browser-side formatting, matching the calendar behavior.

### Scope
- Preserve prediction lock rules based on the real match start time minus 5 minutes.
- Display kickoff time and `Editar hasta` time in the viewer timezone.
- Group and filter prediction date chips by viewer local date.
- Preserve active chip centering and date-chip navigation behavior.
- Add feature coverage for Europe/Madrid display and UTC-midnight boundaries.
- Add Playwright timezone emulation for prediction smoke coverage.

### Out of scope
- No scoring changes.
- No prediction submission/edit rule changes.
- No league, API-Football sync, auth, production, or Railway configuration changes.

### Acceptance criteria
- `/predictions` and `/calendar` show consistent local times for the same fixture.
- Argentina vs Algeria at `2026-06-17 01:00 UTC` shows `03:00` in Europe/Madrid.
- Edit-until time for that fixture shows `02:55` in Europe/Madrid.
- UTC-midnight boundary fixtures appear under the viewer's local date.

### Suggested commit message
Fix predictions local timezone display

### Ticket ID
QA-01

### Title
Add deep QA checklist and automated smoke coverage

### Status
Done

### Sprint
Sprint 8

### Priority
High

### Objective
Create a production-readiness QA checklist and expand stable Playwright smoke coverage without changing product behavior.

### Note
Implemented with `docs/qa-checklist.md`, covering manual QA for auth, predictions, calendar, private leagues, leaderboards, admin, API-Football integrity, mobile/responsive behavior, and abuse protection. Playwright smoke coverage now checks login/register/forgot-password pages, dashboard access, predictions date chips and match cards, prediction date navigation, calendar, leaderboard, admin API health access, and non-admin denial for admin API health.

### Scope
- Add actionable deep QA checklist.
- Document recommended local QA reset/build/test flow.
- Extend e2e smoke tests using demo users and demo data.
- Avoid real Google, Brevo, and API-Football calls in automation.

### Out of scope
- No scoring changes.
- No prediction logic changes.
- No API sync mapping changes.
- No auth provider changes.
- No league business rule changes.

### Acceptance criteria
- `docs/qa-checklist.md` exists and is actionable.
- Existing and new e2e smoke tests are stable against demo data.
- Laravel tests and frontend build still pass.

### Suggested commit message
Add deep QA checklist and smoke coverage

## EPIC 17 - Dashboard vivo, identidad y engagement

### Ticket ID
E17-T01

### Title
Document live dashboard narrative

### Status
Todo

### Sprint
Next sprint

### Priority
High

### Objective
Define the product narrative for the new dashboard. Inicio should stop being a generic access hub and become an actionable/social dashboard.

### Scope
- Document that the dashboard should focus on pending predictions, live-ish match tracking, private league activity, and compact league summary.
- Document that generic filler statistics should be avoided.
- Document that empty filler cards should not be shown.
- Document that the large "Hola" hero should be replaced by a compact mobile-first structure.

### Out of scope
- No application code.
- No UI implementation.
- No database changes.

### Acceptance criteria
- The backlog clearly documents the new dashboard direction.
- The ticket can guide future implementation tickets.

### Suggested commit message
docs: add live dashboard engagement roadmap

### Ticket ID
E17-T02

### Title
Add predefined profile avatar system

### Status
Done

### Sprint
Next sprint

### Priority
High

### Objective
Allow users to choose a predefined local avatar for profile identity without uploading custom images and without using Google profile photos.

Implemented the backend foundation with a nullable `users.profile_avatar_key` field, `User` helpers for avatar choice state and rendering metadata, validation through `App\Support\ProfileAvatarCatalog`, and a compact `<x-profile-avatar>` Blade component that falls back to the local default/silhouette asset when the user has not chosen an avatar or has an invalid stored key. `null` remains the unselected state, while `default` is an explicit user choice.

### Scope
- Add a user avatar key field or equivalent.
- Use null to mean the user has not chosen yet.
- Use a default/silhouette option to mean the user explicitly chose no profile image.
- Store avatar choices as keys mapped to local assets.
- Do not use Google profile pictures.
- Do not allow image uploads.
- Prepare avatar rendering for header, rankings, leagues, and social dashboard cards.

### Out of scope
- No free-form image upload.
- No Google avatar import.
- No external storage.
- No scoring, prediction, league, API-Football, auth-provider, or production config changes.

### Acceptance criteria
- Users can have a stored avatar key.
- A default/silhouette choice is supported.
- Missing/unselected avatar state can be distinguished from "default selected".
- Existing users remain valid.

### Suggested commit message
feat: add predefined profile avatars

### Ticket ID
E17-T03

### Title
Prompt users to choose avatar

### Status
Done

### Sprint
Next sprint

### Priority
High

### Objective
Show a profile avatar selection modal to users who have not chosen an avatar yet.

Implemented with an authenticated `PATCH /profile/avatar` route (`profile.avatar.update`) that validates choices against `App\Support\ProfileAvatarCatalog`, stores only configured local avatar keys, and accepts `default` as an explicit choice. Added a reusable avatar selection form, a verified-app layout prompt for users whose `profile_avatar_key` is still null, and a simple avatar section on the profile edit page so users can change the choice later.

### Scope
- Show the modal only when the authenticated user's avatar choice is missing/null.
- Let the user choose one predefined avatar or the default/silhouette option.
- After choosing any option, do not show the modal again automatically.
- Provide a way to change the avatar later from profile or the user menu.
- Keep copy in Spanish and aligned with Prode tone.

### Out of scope
- No forced upload.
- No Google avatar usage.
- No blocking prediction, league, or dashboard access after dismissal/selection.
- No scoring or prediction logic changes.

### Acceptance criteria
- Users without avatar selection see the modal after login/dashboard access.
- Users who select an avatar no longer see the modal.
- Users who select the default/silhouette option no longer see the modal.
- Users can later change avatar from profile/menu.

### Suggested commit message
feat: prompt users to choose avatar

### Ticket ID
E17-T04

### Title
Add local avatar assets and catalog

### Status
Done

### Sprint
Next sprint

### Priority
Medium

### Objective
Add the first local avatar asset set and a maintainable avatar catalog.

### Note
Implemented the avatar catalog foundation with the corrected prepared local asset set: `public/avatars/0.png` as the default/silhouette option and `public/avatars/1.png` through `public/avatars/9.png` as selectable predefined avatars. Added `config/profile-avatars.php` with Spanish labels and `App\Support\ProfileAvatarCatalog` helpers for listing, lookup, validation, default retrieval, public paths, and asset URLs. Added focused tests proving the catalog shape, valid/invalid keys, default avatar, and configured asset existence.

### Scope
- Add local avatar assets under `public/avatars` or an equivalent public asset path.
- Include the prepared corrected set of 9 selectable avatars and 1 default/silhouette avatar.
- Add a config/catalog file such as `config/profile-avatars.php` with keys, labels, and asset paths.
- Add tests or validation ensuring configured assets exist.
- Keep assets generic and not based on real people.

### Out of scope
- No user-uploaded images.
- No third-party avatar API.
- No copyrighted/proprietary character assets.
- No scoring, prediction, league, or API changes.

### Acceptance criteria
- Avatar catalog exists.
- Each configured avatar points to an existing local file.
- Default/silhouette avatar exists.
- The system can render avatar options from the catalog.

### Suggested commit message
feat: add local avatar catalog

### Ticket ID
E17-T05

### Title
Add dashboard engagement demo data

### Status
Done

### Sprint
Next sprint

### Priority
High

### Objective
Create or extend local/staging demo data so the new dashboard can be tested with realistic engagement scenarios.

Implemented by extending `Database\Seeders\StagingDemoSeeder` only. The deterministic local/staging dataset now includes demo users with `profile_avatar_key` null, `default`, and valid avatar keys; a shared private league with five active members; a four-match next engagement day with main-user missing-prediction gaps and friend completion counts of 4/4, 3/4, 2/4, and 0/4; live-ish partial-score matches with `api_status` values; and additional finished/scored matches for future GF/GC and recent-form dashboard work. Existing safe reset guards and demo credentials remain unchanged.

### Scope
- Include users with and without avatar choices.
- Include upcoming matches.
- Include the nearest day with partially completed predictions.
- Include in-progress/live-ish matches.
- Include finished matches for GF/GC averages and form indicators.
- Include private leagues with multiple members.
- Include members with different prediction completion counts for the next match day, such as 4/4, 3/4, 2/4, and 0/4.
- Include predictions that are provisionally exact, trend-correct, incorrect, and missing for in-progress matches.

### Out of scope
- No production seeders.
- No destructive production commands.
- No real API calls.
- No scoring rule changes.

### Acceptance criteria
- Local/staging demo data supports dashboard visual QA.
- Existing staging reset safety protections remain intact.
- Production/live destructive protections remain intact.

### Suggested commit message
feat: add dashboard engagement demo data

### Ticket ID
E17-T06

### Title
Build live dashboard data service

### Status
Done

### Sprint
Next sprint

### Priority
High

### Objective
Create a backend data preparation layer for the redesigned dashboard, keeping complex queries out of Blade views.

Implemented `App\Services\Dashboard\LiveDashboardDataService` as a plain array data-preparation layer and wired it into `DashboardController` as `liveDashboardData` without redesigning the current Blade view. The service prepares nearest-day missing predictions with local timezone URLs, conservative live-ish match states, provisional prediction status, friend completion activity across shared private leagues with deduplication, compact league summaries using existing ranking order, and GF/GC averages from finished matches.

### Scope
- Determine the nearest local match day with open/predictable matches missing a prediction from the current user.
- Return only missing predictions from that nearest relevant day.
- Return in-progress matches with current score, user's prediction, and provisional status: exact, trend, incorrect, or no prediction.
- Return last API sync age for live-ish match display when available.
- Return private-league friend activity for the next match day, deduplicating users across leagues.
- Sort friends by completed prediction count descending.
- Return compact league summary data.
- Calculate GF/GC averages from finished tournament matches where enough data exists.

### Out of scope
- No dashboard UI redesign in this ticket.
- No scoring changes.
- No prediction save/edit changes.
- No API-Football endpoint additions.
- No cron or Railway changes.

### Acceptance criteria
- Service/query object returns stable data for the dashboard.
- Tests cover pending predictions, in-progress status, friend activity deduplication, and GF/GC calculation.
- Blade views are not filled with complex query logic.

### Suggested commit message
feat: add live dashboard data service

### Ticket ID
E17-T07

### Title
Redesign dashboard as mobile-first live home

### Status
Done

### Sprint
Next sprint

### Priority
High

### Objective
Replace the current generic dashboard with a mobile-first dashboard focused on action, live-ish match tracking, and private-league social activity.

Implemented a mobile-first `/dashboard` live home backed by `liveDashboardData`. The old large greeting hero and generic metric cards were replaced with conditional modules for nearest-day missing predictions, compact live-ish match tracking, private-league friend completion activity, and a secondary league summary. Pending match rows link directly to the prepared `/predictions` date/timezone URL, empty modules are hidden, and the existing route/auth/avatar prompt behavior remains unchanged.

### Scope
- Redesign `/dashboard`.
- Use a compact header with logo/title and a top-right hamburger/user menu.
- Do not add bottom navigation.
- Remove the large "Hola" hero.
- Replace generic metric cards with contextual modules.
- Show "Te falta pronosticar" only when pending predictions exist for the nearest relevant day.
- Show only missing predictions from that day.
- Make pending match rows/cards clickable to the correct `/predictions` date.
- Show "En juego" only when there are in-progress/live-ish matches.
- Show "Actualizado hace X min" instead of formal live disclaimers.
- Show provisional status indicators for the user's current prediction state.
- Show "Tus amigos ya se movieron" only when private-league friend activity exists.
- Show up to 6 visible friends, with internal scroll if needed.
- Show compact league summary.
- Keep Spanish copy and avoid gambling language.

### Out of scope
- No scoring changes.
- No prediction validation changes.
- No API-Football sync changes.
- No full redesign of predictions, leagues, calendar, history, or admin.
- No React, Vue, or Inertia.

### Acceptance criteria
- Dashboard is useful on mobile.
- Empty/non-applicable modules are hidden rather than replaced by filler.
- Pending prediction links route to the correct date on `/predictions`.
- Live-ish matches show current score, user prediction, provisional status, and sync age.
- Friend activity does not reveal prediction values.
- Existing auth/admin access rules remain unchanged.

### Suggested commit message
feat: redesign live dashboard

### Ticket ID
E17-T08

### Title
Add recent form indicators to rankings

### Status
Done

### Sprint
Next sprint

### Priority
Medium

### Objective
Make league rankings more engaging by showing each user's recent prediction form.

Implemented with `App\Services\Rankings\RecentFormService`, which attaches compact recent-form states to existing leaderboard entries without changing ranking queries or order. The shared `x-ranking-table` now renders a small recent-form column when form data exists, using the latest finished/scored match sequence for all users and states for exact, trend, incorrect, and no prediction. The league hub, private league detail, and `/leaderboard` all use the computed indicators.

### Scope
- Add compact recent form indicators to general and private league rankings.
- Use the latest shared finished/scored matches as the comparison basis.
- Use the same match sequence for every user in a given ranking.
- Show indicators:
  - exact result
  - trend correct
  - incorrect
  - no prediction
- Keep points, exacts, trends, and scored prediction counts unchanged.
- Add a small legend if needed.

### Out of scope
- No ranking sort changes.
- No scoring rule changes.
- No new badges or rewards.
- No prediction logic changes.

### Acceptance criteria
- Rankings still sort exactly as before.
- Users can quickly see recent form.
- No prediction values are leaked before allowed visibility.
- Removed private league members remain excluded.

### Suggested commit message
feat: add ranking form indicators

### Ticket ID
E17-T09

### Title
QA dashboard engagement sprint

### Status
Done

### Sprint
Next sprint

### Priority
High

### Objective
Validate the avatar, dashboard, demo data, and ranking-form changes before staging/production release.

Completed QA closure for EPIC 17 by reviewing and tightening `docs/qa-checklist.md` coverage for avatar prompt behavior, dashboard live-home modules, prediction date/timezone links, friend activity privacy, hidden empty modules, and ranking recent-form invariants. Validation passed with `php artisan test`, `npm run build`, and `git diff --check`. No follow-up tickets were needed.

### Scope
- Update `docs/qa-checklist.md` if flows change.
- Add/adjust feature tests where needed.
- Add/adjust Playwright smoke coverage for dashboard and avatar modal where stable.
- Verify `php artisan test`.
- Verify `npm run build`.
- Verify local/staging smoke flow.
- Document any follow-up issues in `docs/backlog.md`.

### Out of scope
- No production deploy.
- No production E2E writes.
- No unrelated UI redesign.

### Acceptance criteria
- Local tests pass.
- Frontend build passes.
- Staging QA path is documented.
- Follow-up bugs are captured in the backlog.

### Suggested commit message
test: cover dashboard engagement sprint

## EPIC 18 - Dashboard responsive, jornada y activación social

Product direction:
The dashboard should work well in two major states:
- Users with useful activity/social context.
- Users with no private leagues, where the dashboard would otherwise feel empty.

The dashboard must keep "Te falta pronosticar" as the main action when pending predictions exist, use desktop width better, and provide a compact right-side context column.

Product rules:
- Do not use gambling/betting language.
- Use Spanish copy.
- Keep mobile-first behavior.
- Desktop should use a two-column structure where appropriate.
- Mobile should remain stacked and easy to scan.
- Empty/filler cards should still be avoided, but meaningful onboarding/activation cards are allowed.
- No scoring, prediction rules, league business rules, API sync, auth, Railway, production config, migrations, or database changes are implied by this planning epic.

### Ticket ID
E18-T01

### Title
Document dashboard responsive and social activation roadmap

### Status
Done

### Sprint
Next sprint

### Priority
High

### Objective
Document the agreed product direction for the next dashboard iteration after EPIC 17.

Documented EPIC 18 as the post-EPIC 17 roadmap for dashboard responsive layout, daily World Cup context, compact prediction-state indicators, and private-league social activation.

### Scope
- Document the desktop layout direction:
  - main content area using an 8/12 column for "Te falta pronosticar"
  - right sidebar using a 4/12 column for compact tournament/day context
- Document mobile stacking behavior.
- Document that "Te falta pronosticar" remains the primary action when pending predictions exist.
- Document that "Hoy en el Mundial" replaces the narrower "En juego" concept.
- Document that "Jugá con tus amigos" is a full-width onboarding/social activation card shown only to users with no active private leagues.
- Document that users with private leagues should see friend activity instead of the onboarding card.
- Document compact visual prediction-state indicators:
  - gray dot for no prediction
  - green dot for trend/correct direction
  - red dot for incorrect
  - violet star for exact
- Document that repeated visible text like "Sin pronóstico" should be avoided in compact match rows.

### Out of scope
- No application code.
- No UI implementation.
- No data service changes.
- No tests required unless docs checks exist.

### Acceptance criteria
- EPIC 18 clearly captures the agreed roadmap.
- Future implementation tickets can be executed independently.

### Suggested commit message
docs: add dashboard responsive activation roadmap

### Ticket ID
E18-T02

### Title
Add daily World Cup sidebar card to dashboard

### Status
Done

### Sprint
Next sprint

### Priority
High

### Objective
Replace the current narrow "En juego" concept with a compact right-side dashboard card showing the user's local World Cup day context.

Implemented by extending `App\Services\Dashboard\LiveDashboardDataService` with `daily_matches`, a plain-array dashboard data section that selects the relevant viewer-local match day from actual fixture dates and includes scheduled, live-ish, and finished matches. The `/dashboard` Blade view now renders a compact "Hoy en el Mundial" card using kickoff times for scheduled rows, scores/status/sync age for live-ish rows, and scores for finished rows, while preserving pending-prediction behavior and existing dashboard route wiring.

### Scope
- Add/adjust dashboard data so the card can show matches for the relevant local day.
- The card should be called "Hoy en el Mundial" unless product copy changes.
- Show matches from the user's local day, including:
  - scheduled/upcoming matches
  - live-ish/in-progress matches
  - finished matches
- If the next local day has no matches, continue showing the last relevant match day until a future match day exists.
- When the local day changes and there are matches, move to that day.
- Keep rows compact, like a sidebar.
- Show team codes/names, kickoff time or score, and match status where useful.
- Show last sync age when useful and available, especially for live-ish data.
- A match may appear both in "Te falta pronosticar" and "Hoy en el Mundial" if it is part of the relevant day and the user has not predicted it yet.

### Out of scope
- No scoring changes.
- No prediction lock rule changes.
- No API-Football sync changes.
- No cron/Railway changes.
- No production config changes.
- No broad dashboard redesign beyond this card and required data.
- No external packages.

### Acceptance criteria
- Dashboard shows a compact "Hoy en el Mundial" card on desktop/right sidebar and stacked on mobile.
- The card can show upcoming, live-ish, and finished matches.
- It does not become an empty large card just because there are no live matches.
- Existing live-ish behavior remains conservative and sync-age aware.

### Suggested commit message
feat: add daily matches dashboard card

### Ticket ID
E18-T03

### Title
Improve dashboard desktop grid layout

### Status
Done

### Sprint
Next sprint

### Priority
High

### Objective
Use desktop width better by moving from fully stacked cards to a responsive dashboard grid.

Implemented as a Blade-only dashboard layout update: the top dashboard modules now use a conditional responsive grid with "Te falta pronosticar" in the main 8/12 desktop column when present and "Hoy en el Mundial" plus "Tus amigos ya se movieron" stacked in a compact 4/12 sidebar. Mobile remains stacked in the existing action-first order, and no empty sidebar column is reserved when sidebar modules are absent.

### Scope
- On desktop, arrange the first dashboard section as:
  - 8/12 width: "Te falta pronosticar"
  - 4/12 width: "Hoy en el Mundial"
- On mobile, keep sections stacked.
- Keep "Te falta pronosticar" visually dominant.
- Ensure the right column behaves like a compact sidebar, not a full-width content block.
- If the user has private leagues, place "Tus amigos ya se movieron" as another right-column sidebar card when appropriate.
- If the user has no private leagues, do not show the friend activity card.
- Preserve existing dashboard route and access rules.

### Out of scope
- No changes to prediction save/edit behavior.
- No scoring changes.
- No league business rule changes.
- No API sync changes.
- No migrations.
- No redesign of other pages.

### Acceptance criteria
- Desktop dashboard uses a clear 8/12 + 4/12 layout where data exists.
- Mobile remains stacked and readable.
- "Hoy en el Mundial" and friend activity cards feel like sidebar modules.
- No empty filler modules are introduced.

### Suggested commit message
style: improve dashboard desktop grid

### Ticket ID
E18-T04

### Title
Add private league onboarding CTA to dashboard

### Status
Done

### Note
Implemented the "Jugá con tus amigos" onboarding card on the dashboard for users without active private leagues. The card explains the social flow to create a league, copy/share the invite link, and compete with friends, using polished inline SVG steps and existing league routes. It is hidden for users with active private leagues and does not change league creation, invitation approval rules, scoring, predictions, API sync, auth, migrations, or production config.

### Sprint
Next sprint

### Priority
High

### Objective
Add a strong social onboarding card for users who do not belong to any active private league.

### Product narrative
The card should be a protagonist onboarding/social activation piece, not a small empty-state placeholder. It should explain how to play with friends:
- Create a league.
- Copy the invite link.
- Share it with friends.
- Compete together in the ranking.

### Scope
- Show the card only when the authenticated user has no active private league memberships.
- Do not show it to users who already belong to at least one active private league.
- On desktop, place it below the top dashboard row and let it occupy the full available width.
- On mobile, stack it after the main daily/action modules.
- Suggested title: "Jugá con tus amigos".
- Suggested subtitle: "Creá tu propia liga, compartí el link y competí con tu grupo durante el Mundial."
- Include a short step-by-step:
  - "Creá tu liga" - "Elegí el nombre que más te guste."
  - "Copiá el link" - "El sistema genera un link para invitar."
  - "Compartilo con tus amigos" - "Mandalo por WhatsApp, Telegram o donde quieras."
  - "Compitan en su ranking" - "Tus amigos piden entrar y juegan en la misma tabla."
- Include a short note:
  - "Tus amigos se suman desde un link y vos aprobás el ingreso."
- Include clear CTAs:
  - primary: "Crear mi liga"
  - secondary: "Buscar liga" or "Tengo un código"
- Icons may be inline SVGs, but they must be visually polished, consistent, and aligned with the Prode style.
- The card must feel premium, friendly, and useful, because it is the main welcome content for users with no social activity.

### Out of scope
- No changes to league creation business rules.
- No automatic joining.
- No bypassing owner approval.
- No changes to invitation-link logic.
- No external icon packages.
- No image upload.
- No scoring, predictions, API sync, auth, Railway, production config, or migrations.

### Acceptance criteria
- Users with no active private leagues see the onboarding CTA card.
- Users with active private leagues do not see it.
- The card is visually important and not a small placeholder.
- The card works well on mobile and desktop.
- CTA links go to the correct existing create/search/join league flows.
- Existing private league approval/invitation rules remain unchanged.

### Suggested commit message
feat: add league onboarding dashboard card

### Ticket ID
E18-T05

### Title
Compact dashboard prediction state indicators

### Status
Done

### Sprint
Next sprint

### Priority
Medium

### Objective
Make compact dashboard match rows easier to scan by replacing repeated state text with small visual indicators.

### Note
Implemented compact dashboard indicators in the "Hoy en el Mundial" rows. Replaced repeated visible state labels such as "Sin pronóstico" with accessible visual indicators: violet star for exact, green dot for trend/correct direction, red dot for incorrect, and gray dot for no prediction. Accessibility is preserved with title, aria-label, and sr-only labels. No scoring, prediction rules, layout, data service, or business logic changes were made.

### Scope
- In compact dashboard match rows, avoid repeated visible labels such as "Sin pronóstico".
- Use compact indicators:
  - gray dot = no prediction
  - green dot = trend/correct direction
  - red dot = incorrect
  - violet star = exact
- Use accessible labels/title text where needed, but avoid visible legend clutter.
- Apply to "Hoy en el Mundial" and any compact live/daily dashboard rows where the current text takes too much space.
- Preserve meaning for provisional vs final states:
  - if match is live-ish, indicators are provisional
  - if match is finished/scored, indicators represent the final scored prediction state when available

### Out of scope
- No scoring changes.
- No prediction value visibility changes before allowed time.
- No ranking indicator changes unless already using a shared compact indicator component and safe to reuse.
- No dashboard data service rewrite unless needed.

### Acceptance criteria
- Compact rows no longer repeat long state text.
- Visual indicators are clear, consistent, and accessible.
- Prediction values are not leaked before allowed visibility rules.
- Live-ish indicators do not imply final points.

### Suggested commit message
style: compact dashboard prediction indicators

### Ticket ID
E18-T06

### Title
Remove duplicate dashboard logo

### Status
Done

### Note
Removed the duplicated Prode logo from the internal dashboard header while keeping the global navigation logo unchanged. The dashboard header now keeps the "INICIO" eyebrow, "Mi Prode" title, and existing admin/avatar area. No dashboard modules, responsive grid, compact indicators, business logic, or shared navigation behavior were changed.

### Sprint
Next sprint

### Priority
Medium

### Objective
Clean up the dashboard header by removing the duplicated logo inside the internal dashboard header/card.

### Scope
- Keep the global navigation logo unchanged.
- Remove the repeated logo from the internal dashboard header/card.
- Keep the internal dashboard header with:
  - eyebrow/caption such as "INICIO"
  - title "Mi Prode"
  - existing user/avatar/admin badge area if present
- Improve spacing, especially on mobile.

### Out of scope
- No broad navigation redesign.
- No dashboard feature changes.
- No avatar logic changes.
- No route, auth, scoring, prediction, league, API sync, Railway, production config, or migration changes.

### Acceptance criteria
- The logo is not visually repeated in the dashboard header.
- The dashboard header remains recognizable and clean.
- Existing nav/header behavior remains unchanged.

### Suggested commit message
style: remove duplicate dashboard logo

### Ticket ID
E18-T07

### Title
QA dashboard responsive and social activation

### Status
Done

### Sprint
Next sprint

### Priority
High

### Objective
Validate the EPIC 18 dashboard changes locally and in staging before any production release.

Closed EPIC 18 QA/documentation by updating `docs/qa-checklist.md` with responsive dashboard layout, `Hoy en el Mundial`, compact indicators, friend activity, onboarding CTA, CTA route, and duplicate-logo checks. User-confirmed local validation passed after E18-T04 with `php artisan test`, `npm run build`, `git diff --check`, and `PLAYWRIGHT_BASE_URL=http://127.0.0.1:8102 npm run test:e2e`.

### Scope
- Update `docs/qa-checklist.md` with EPIC 18 checks if needed.
- Validate desktop layout:
  - 8/12 pending predictions
  - 4/12 daily sidebar
  - friend activity sidebar only when user has private leagues
  - league onboarding card full-width only when user has no private leagues
- Validate mobile stacked layout.
- Validate "Hoy en el Mundial" behavior for scheduled, live-ish, and finished matches.
- Validate compact prediction indicators.
- Validate the onboarding card CTAs.
- Run:
  - `php artisan test`
  - `npm run build`
  - `PLAYWRIGHT_BASE_URL=http://127.0.0.1:8100 npm run test:e2e`
- After local validation, run staging QA before production.

### Out of scope
- No production deploy.
- No production E2E writes.
- No new feature work.
- No unrelated UI redesign.

### Acceptance criteria
- Local tests pass.
- Frontend build passes.
- Local Playwright passes or any failures are documented/fixed.
- Staging QA path is documented.
- Follow-up bugs are captured in `docs/backlog.md`.

### Suggested commit message
test: cover dashboard responsive activation

## EPIC 19 - Ranking, ligas y gestión social

### Objective
Improve the rankings, private leagues and social-management UX so league activity feels more human, readable and easy to manage on mobile and desktop.

### Context
The app already has Liga general, private leagues, owner-approved join requests, invitation links, league rankings, `/leagues` as the primary ranking/leagues hub, and a mobile-first Blade/Tailwind UI. This epic tracks the next visual and interaction polish so implementation does not rely on chat memory.

### Product direction correction
The private league detail screen must prioritize the ranking. Users mainly enter this page to see who is winning, so the top area should be aggressively simplified.

Do not give main visual priority to low-value league metadata such as:
- owner display name
- owner `@username`
- member count
- "Active" status
- large cards for code/status/members

The desired flow is:
- enter league detail
- see league name quickly
- see ranking as soon as possible
- if owner, copy invite code/link easily through a compact action
- if owner, manage pending requests easily through a future header-level alert/modal

For the league header:
- keep only a compact "Liga privada" label and league name
- optional subtle visual dressing is acceptable, such as a faint trophy/world-cup watermark
- code/link copy must be compact, not a big card or dominant CTA
- no repeated preamble before the ranking

For owner management:
- pending request management must not be hidden in an info button
- future pending requests UI should live near the app header/hamburger menu as an alert with badge and modal/sheet
- the info "i" button, if used, should only contain contextual explanation, not critical actions

### General constraints
- Keep Blade + Tailwind + vanilla JS.
- Mobile-first.
- No React, Vue, Inertia or external packages.
- Avoid gambling/betting language.
- Keep production safety rules.
- Validate locally and in staging before production.
- Do not deploy production as part of these tickets.

### Ticket ID
E19-T01

### Title
Refactor visual de tablas de posiciones general y privadas

### Status
Done

### Sprint
Next sprint

### Priority
High

### Objective
Replace the cramped conventional standings table with a more readable mobile-first ranking layout used consistently for both Liga general and private leagues.

### Scope
- Apply the same visual ranking style to Liga general and private league standings.
- Use a ranking/card-table hybrid instead of a cramped horizontal table on mobile.
- Show position, avatar, human display name, `@username`, points, exacts, trends, scored predictions and recent form.
- In the identity block, show only human display name as primary text and `@username` as secondary text.
- Do not add third-line status labels such as "MIEMBRO ACTIVO", "PRIMER PUESTO" or similar under the username; they add scanning noise.
- Represent first place visually through the position badge/row highlight.
- Represent the current user visually through the `VOS`/`TÚ` badge or subtle row highlight.
- Active membership is already implied in private league rankings and does not need text.
- Keep current user highlight.
- Keep first-place highlight.
- Preserve existing ranking order, scoring, aggregation and tie behavior.
- Preserve existing routes and business logic.

### Out of scope
- No scoring changes.
- No prediction logic changes.
- No database changes.
- No league membership rule changes.
- No new ranking algorithm.
- No external packages.

### Acceptance criteria
- Liga general and private league standings use the same readable ranking layout.
- Mobile no longer feels like a cramped horizontal stats table.
- Human display names are primary and `@username` remains visible as secondary text.
- Ranking identity blocks do not show extra status/role text below the username.
- Current user and first-place highlights remain intact.
- Ranking order, scoring, aggregation and tie behavior are unchanged.

### Implementation notes
- Ranking tables were refactored into a cleaner mobile-first ranking/card-table hybrid layout.
- Applies to general rankings and private league rankings.
- Rows prioritize position, avatar, display name, `@username`, points, exacts, trends, scored predictions and recent form.
- Identity block now shows at most human display name + `@username`.
- Removed noisy third-line labels such as "MIEMBRO ACTIVO" and "PRIMER PUESTO".
- First place/current user are handled through visual highlight/badge, not extra text under the username.
- Ranking order, scoring, aggregation and tie behavior were preserved.

### Validation
- `php artisan view:cache`
- `php artisan test tests/Feature/PrivateLeagueLeaderboardTest.php tests/Feature/RecentRankingFormTest.php tests/Feature/LeaguesHubTest.php`
- `npm run build`
- `git diff --check`

### Suggested commit message
style: refactor ranking table layout

### Ticket ID
E19-T02

### Title
Rediseñar detalle de liga privada y reducir preámbulo

### Status
Done

### Sprint
Next sprint

### Priority
High

### Objective
Simplify the private league detail screen so ranking appears sooner and the page has less repeated header/card preamble.

### Scope
- Unify the top title area into a compact league header that prioritizes only "Liga privada" and the league name.
- Remove or replace the oversized "Ver ligas" CTA with a subtle navigation pattern.
- Avoid prominent owner/name, member-count, status or large metadata cards before the ranking.
- Make the ranking the main protagonist of the screen.
- Keep owner/member permissions unchanged.
- Keep mobile-first layout.

### Out of scope
- No route changes unless strictly necessary.
- No scoring/ranking logic changes.
- No league rule changes.

### Acceptance criteria
- Private league detail shows the ranking sooner.
- The league header is compact and avoids repeated title/preamble content.
- Navigation back to leagues remains available but visually subtle.
- Owner/member permissions and league business rules are unchanged.

### Implementation notes
- Private league detail was cleaned so the ranking appears sooner and is the protagonist.
- Removed low-value metadata from the top area:
  - owner display name
  - owner `@username`
  - member count
  - "Active" status
  - large code/status/member cards
- Header now keeps only a compact "Liga privada" label and league name.
- Owner sees only a compact inline code/copy action, not a large card or CTA.
- Existing owner management functionality was preserved while later tickets move requests to the header.
- Ranking component and ranking logic were preserved.

### Validation
- `php artisan view:cache`
- `php artisan test tests/Feature/PrivateLeagueLeaderboardTest.php tests/Feature/RecentRankingFormTest.php tests/Feature/LeaguesHubTest.php`
- `npm run build`
- `git diff --check`

### Suggested commit message
style: simplify private league detail

### Ticket ID
E19-T03

### Title
Agregar acciones rápidas para dueño de liga

### Status
In Progress

### Sprint
Next sprint

### Priority
High

### Objective
Replace the current hidden/accordion-heavy owner management experience with clear compact owner actions.

### Scope
- For the owner of a private league, expose compact actions near the league header:
  - copy league code or invite link
  - information button using only an "i" icon
- Keep actions visible and easy to access on mobile.
- The "i" button should summarize contextual league information that is currently buried in the accordion.
- Do not move request approval into the info button.
- Keep copy/link actions compact; do not turn code/status/member metadata into dominant cards.

### Out of scope
- No member removal rule changes.
- No invitation business rule changes.
- No automatic joining.

### Acceptance criteria
- League owners can quickly copy the league code or invite link from the league header area.
- The information button is compact, icon-only, and easy to find on mobile.
- Contextual league information is available without forcing users through a large management accordion.
- Pending request approval remains outside the info button.

### Notes
- Partially covered by E19-T02: the owner now has a compact code/copy action in the private league header.
- Keep this ticket open because the current definition still expects additional owner quick actions/contextual info, including an information button and reducing reliance on the management accordion.
- Do not mark fully Done until those remaining owner quick-action/contextual-info requirements are implemented.

### Suggested commit message
feat: add private league owner quick actions

### Ticket ID
E19-T04

### Title
Gestionar solicitudes pendientes desde alerta en header

### Status
Done

### Sprint
Next sprint

### Priority
High

### Objective
Make pending join requests visible to the league owner from the app header, next to the hamburger menu.

### Scope
- If the authenticated user owns a private league and has pending join requests, show an alert/notification button near the hamburger menu.
- The user can own only one private league, so the alert can safely refer to that league.
- The alert shows a badge with pending count.
- Tapping/clicking opens a modal/sheet with pending request profiles.
- Each request row shows avatar, human display name, `@username`, approve and reject actions.
- Preserve existing approval/rejection behavior and validations.
- The requests panel should live at header/app-shell level, not inside the league card.

### Out of scope
- No email notifications.
- No push notifications.
- No database changes unless absolutely required.
- No membership rule changes.

### Acceptance criteria
- League owners with pending join requests see a header-level alert.
- The alert badge shows the pending request count.
- The modal/sheet lists pending request profiles with avatar, human display name and `@username`.
- Approve/reject actions reuse existing behavior and validations.
- Users without owned pending requests do not see the alert.

### Implementation notes
- Added a header-level pending join requests alert for private league owners.
- Alert appears only when the authenticated user owns a private league with pending join requests.
- Visual trigger was polished into a compact icon-only bell/notification button with count badge near the header controls/hamburger.
- Clicking opens a modal/sheet using the existing modal pattern.
- Modal lists pending requesters with avatar, display name, optional `@username`, and approve/reject actions.
- Approval/rejection reuse existing routes and controller behavior.
- Alert disappears after pending requests are handled and the page is refreshed.
- No changes to league membership rules, scoring, predictions, API sync, database, migrations or production config.

### Validation
- `php artisan view:cache`
- `php artisan test tests/Feature/LeagueHeaderPendingRequestsTest.php`
- `php artisan test --filter=League`
- `npm run build`
- `git diff --check`

### Suggested commit message
feat: surface league join requests in header

### Ticket ID
E19-T05

### Title
Reemplazar acordeón de gestión por información contextual

### Status
Todo

### Sprint
Next sprint

### Priority
Medium

### Objective
Reduce the current "Gestionar liga" accordion dependency by moving important owner actions into visible controls and keeping only contextual information behind an "i" button.

### Scope
- The "i" button should summarize how the league works:
  - invite/copy code or link
  - approval requirement
  - member limits if already surfaced
  - owner role basics
- Remove duplicated or low-value management content from the main flow if it becomes redundant.
- Keep removal/audit behavior intact if currently available.
- Do not hide critical pending-request actions inside the info content.
- Pending request management must remain outside contextual info and should align with the future header-level alert/modal direction.

### Out of scope
- No business rule changes.
- No new notification system.
- No admin features.

### Acceptance criteria
- The private league screen no longer depends on a large management accordion for basic contextual information.
- Important owner actions are visible through compact controls.
- The "i" button provides clear contextual information without hiding pending-request actions.
- Removal/audit behavior remains available if currently supported.

### Notes
- Still pending as final cleanup.
- Now that requests are surfaced in the header, this ticket should focus on removing/reducing duplicate accordion content.
- Do not reintroduce heavy metadata.
- Do not move pending request actions into an info button.
- Keep critical owner functionality accessible.

### Suggested commit message
style: replace league management accordion

## EPIC 20 - Knockout scoring, UX and settlement hardening

### Context
A production incident showed that finished API-Football fixtures can update match status and score without correctly setting `winner_team_id` or settling predictions. This was fixed for group-stage finished matches with the production tag `prod-2026-06-11-api-final-settlement-hotfix`, but knockout matches require additional hardening before elimination rounds begin.

Knockout matches introduce unresolved edge cases around final score, extra time, penalties, qualified team selection, API winner mapping and partial scoring. These rules must be explicitly documented, tested locally and validated in staging before production.

### Product decision
For knockout matches, users still predict the match score using the normal score inputs.

The predicted score represents the final played result before penalties. There is no distinction between a score reached after 90 minutes and a score reached after 120 minutes.

Examples:
- If the user predicts 2-1 and the match is 1-1 after 90 minutes but ends 2-1 after extra time, the predicted score is exact.
- If the match remains tied after extra time, penalties only determine the qualified team.

If the user predicts a draw, the UI must require selecting which team qualifies. The preferred UX is to use the same team/flag blocks as the qualified-team selector.

If the user predicts a non-draw, the qualified team can be inferred from the predicted score winner.

### Proposed knockout scoring matrix

| Case | Exact score | Match trend | Qualified team | Points |
| --- | ---: | ---: | ---: | ---: |
| Perfect prediction | yes | yes | yes | 8 |
| Exact score, wrong qualified team | yes | yes | no | 5 |
| Correct trend and qualified team, not exact | no | yes | yes | 5 |
| Qualified team only | no | no | yes | 3 |
| Match trend only | no | yes | no | 2 |
| Incorrect | no | no | no | 0 |

Definitions:
- Exact score: predicted team A and team B goals match the final played score before penalties.
- Match trend: predicted outcome is team A win, draw, or team B win.
- Qualified team: predicted team that advances to the next round.

### Ticket ID
E20-T01

### Title
Document knockout scoring matrix and UX rules

### Status
Done

### Note
Covered by the documentation commit that added the knockout scoring plan, UX rules, and QA checklist updates.

### Sprint
Post v1 hardening

### Priority
Critical

### Objective
Document the complete knockout prediction rules before changing scoring or UI.

### Scope
- Add the knockout scoring matrix to `docs/backlog.md`.
- Update `docs/qa-checklist.md` with knockout QA scenarios.
- Update product/decision documentation if applicable.
- Clarify that 90-minute and 120-minute results are treated the same.
- Clarify that penalties only determine the qualified team when the final played score is tied.
- Clarify UX behavior for draw predictions and flag-based qualified-team selection.

### Out of scope
- No code changes.
- No scoring changes.
- No UI changes.
- No production or Railway changes.

### Acceptance criteria
- Knockout scoring and UX rules are documented clearly.
- Future implementation tickets can reference this decision.
- No ambiguity remains around extra time, penalties, exact score, trend, and qualified team.

### Suggested commit message
Document knockout scoring and UX rules

### Ticket ID
E20-T02

### Title
Harden API-Football knockout stage detection

### Status
Done

### Note
Centralized round-to-stage normalization in `TournamentMatch::stageFromApiRound()` (single source of truth, used by `api-football:sync-fixtures`). Added stage constants and `TournamentMatch::KNOCKOUT_STAGES`. Fixed `3rd Place Final` mapping (it must be matched before the generic `final` check). Added fraction round labels (`1/16`, `1/8`, `1/4`, `1/2`). Unknown round labels now leave `stage` null, preserve the raw `round`, print a warning, write a `Log::warning`, and are recorded under `metadata.unknown_rounds` in the sync log. Covered by `tests/Unit/TournamentMatchKnockoutTest.php` (round-label → stage → qualified-team requirement for all representative labels) and feature tests in `tests/Feature/ApiFootballSyncFixturesCommandTest.php`. Documented in `docs/api-football.md`. No scoring, schema, UX, or winner-resolution changes (E20-T03 remains separate).

### Sprint
Post v1 hardening

### Priority
Critical

### Objective
Ensure API-Football fixtures are correctly mapped as group-stage or knockout matches.

### Scope
- Inspect current `league.round` / API-Football round mapping.
- Ensure local `matches.stage` is correctly set for group stage, round of 32, round of 16, quarter-final, semi-final, third place, and final.
- Add tests proving group-stage fixtures do not require qualified-team prediction.
- Add tests proving knockout fixtures do require qualified-team prediction.
- Ensure unknown/new round labels are handled conservatively and logged or surfaced for admin review.

### Out of scope
- No scoring matrix changes.
- No UI changes.
- No production or Railway changes.
- No bracket propagation.

### Acceptance criteria
- Group-stage API fixtures map to a non-knockout stage.
- Knockout API fixtures map to knockout stages.
- `TournamentMatch::requiresQualifiedTeamPrediction()` is correct for all supported stages.
- Tests cover representative API-Football round labels.

### Suggested commit message
Harden API knockout stage detection

### Ticket ID
E20-T03

### Title
Harden winner resolution for FT, AET and PEN

### Status
Done

### Note
Replaced the score-only `winnerTeamId()` with `ApiFootballSyncFixturesCommand::resolveWinnerTeamId()`: FT/AET non-draw infers the winner from the played score; group-stage draws keep `winner_team_id` null; tied knockout scores (PEN) resolve the winner from API-Football `teams.home.winner` / `teams.away.winner` flags (home → team A, away → team B), staying null when flags are absent rather than guessing. Live/in-progress statuses still store partial scores without finishing or settling. Settlement remains idempotent (verified by a re-sync test). Covered by feature tests in `tests/Feature/ApiFootballSyncFixturesCommandTest.php` (group FT non-draw + draw, knockout FT/AET non-draw, knockout PEN home/away winner, knockout PEN without flags, live no-settle, idempotent re-sync). No scoring matrix (E20-T04), schema, migration, or UX changes. Documented in `docs/api-football.md`.

### Sprint
Post v1 hardening

### Priority
Critical

### Objective
Ensure finished matches always resolve `winner_team_id` correctly when applicable.

### Scope
- For FT/AET matches with non-draw score, infer winner from final score.
- For group-stage FT/AET draws, keep `winner_team_id` null.
- For knockout PEN matches, resolve `winner_team_id` from API-Football winner flags such as `teams.home.winner` / `teams.away.winner` or equivalent available payload fields.
- Add tests for group FT 2-0 home winner, group FT 1-1 draw with null winner, knockout FT 2-1 home winner, knockout AET 2-1 home winner, knockout PEN tied score with home winner, and knockout PEN tied score with away winner.
- Keep settlement idempotent.
- Do not settle live/in-progress matches.

### Out of scope
- No UI changes.
- No scoring matrix changes beyond preserving current settlement behavior.
- No production or Railway changes.
- No external API calls in tests.

### Acceptance criteria
- Finished matches have correct `winner_team_id`.
- Knockout penalty winners are resolved correctly.
- Group-stage draws keep `winner_team_id` null.
- Live statuses store partial scores if supported but do not settle predictions.
- Tests pass.

### Suggested commit message
Harden finished match winner resolution

### Ticket ID
E20-T04

### Title
Implement expanded knockout scoring matrix

### Status
Done

### Note
Replaced the old knockout 6/3/0 logic in `PredictionScoringService::calculateKnockoutPrediction()` with the expanded matrix (8/5/5/3/2/0) using `predicted_qualified_team_id` vs `matches.winner_team_id`. Group-stage scoring (`calculate()`, 6/3/0) is untouched; routing still goes through `requiresQualifiedTeamPrediction()`. A null `predicted_qualified_team_id` or null `winner_team_id` earns no qualified-team bonus (no guessing) but still scores exact/trend tiers. Added the new point constants. Coverage: every matrix branch plus both null-edge cases in `tests/Unit/PredictionScoringServiceTest.php`; settlement persistence + idempotency in `tests/Feature/AdminResultSettlementTest.php` and `tests/Feature/MatchPredictionSettlementServiceTest.php` (existing knockout test updated to the new matrix). Leaderboards naturally sum the new points — no query changes. No schema, UX, API sync, or winner-resolution changes.

### Sprint
Post v1 hardening

### Priority
Critical

### Objective
Replace the simple knockout 6/3/0 scoring with the expanded matrix that rewards exact score, match trend and qualified team separately.

### Scope
- Update `PredictionScoringService` knockout logic.
- Award 8 for exact score + correct qualified team.
- Award 5 for exact score + wrong qualified team.
- Award 5 for correct trend + correct qualified team without exact score.
- Award 3 for only correct qualified team.
- Award 2 for only correct trend.
- Award 0 for fully incorrect predictions.
- Add unit tests for every scoring branch.
- Add feature tests proving settlement stores the expected points.
- Ensure group-stage scoring remains unchanged at 6/3/0.

### Out of scope
- No UI changes.
- No API sync changes except using existing fields.
- No leaderboard query changes beyond naturally summing points.
- No production or Railway changes.

### Acceptance criteria
- Knockout scoring matrix is fully covered by tests.
- Group-stage scoring remains unchanged.
- Settlement remains idempotent.
- Leaderboards reflect updated points after settlement.

### Suggested commit message
Implement expanded knockout scoring

### Ticket ID
E20-T05

### Title
Polish knockout prediction UX with flag-based qualified selector

### Status
Done

### Note
Added a reusable `<x-knockout-qualified-selector>` (flag/label radio buttons, mobile-first) used by both the inline `/predictions` bulk flow and the single-match form, replacing the old `<select>`. Knockout matches now show helper copy about the final played result, 120' extra time, and penalty qualification (no gambling language). `PredictionController` resolves the qualified team server-side via `resolveKnockoutQualifiedTeam()`: non-draw scores infer the winner (no manual selection needed, works without JS), draws still require an explicit valid selection or fail with a Spanish error. Progressive-enhancement script (`predictions/partials/knockout-inference.blade.php`) auto-selects the inferred team and toggles auto/draw hints; qualified-team radios participate in unsaved-change tracking so the floating save still works. Closed/read-only views (inline card and `/my-predictions`) show the predicted qualified team. Group-stage UX/behavior and bulk-save unchanged. Tests in `tests/Feature/CorePredictionFlowTest.php` and `tests/Feature/KnockoutPredictionFlowTest.php` cover non-draw inference (single + bulk), draw validation/persistence, preload, and read-only display; two pre-existing `KnockoutPredictionFlowTest` cases were updated to the new non-draw-inference rule (and their latent factory-time flakiness fixed). UX rules documented in `docs/ui-guidelines.md`; QA steps refined in `docs/qa-checklist.md`. No scoring, winner-resolution, API sync, schema, or leaderboard changes.

### Sprint
Post v1 hardening

### Priority
High

### Objective
Make knockout prediction UX clear and simple while supporting draw + qualified-team selection.

### Scope
- Keep normal score inputs for all matches.
- If predicted score is not a draw, infer the predicted qualified team from the predicted winner.
- If predicted score is a draw, require the user to select which team qualifies.
- Use team/flag blocks as the preferred selector UI.
- Preload existing predictions including qualified team.
- Show clear read-only summary after prediction close.
- Add Spanish helper copy explaining that 90-minute and 120-minute final played score are treated the same, and penalties only decide who qualifies.
- Validate that draw knockout predictions cannot be saved without qualified team.
- Add focused feature/UI tests where feasible.

### Out of scope
- No scoring changes.
- No API sync changes.
- No bracket visualization.
- No external frontend framework.
- No production or Railway changes.

### Acceptance criteria
- Users can predict knockout matches without understanding internal scoring complexity.
- Draw predictions require qualified-team selection.
- Non-draw predictions infer qualified team safely.
- Closed predictions display score and qualified team clearly.
- Existing group-stage prediction UX is not broken.

### Suggested commit message
Polish knockout prediction UX

### Ticket ID
E20-T06

### Title
Add finished-match consistency checks

### Status
Todo

### Sprint
Post v1 hardening

### Priority
Critical

### Objective
Detect settlement and finished-match inconsistencies before users notice ranking errors.

### Scope
- Add a read-only Artisan command such as `prode:check-finished-matches`.
- Detect finished matches with null scores.
- Detect API FT/AET/PEN matches not locally marked finished.
- Detect finished knockout matches without `winner_team_id`.
- Detect finished matches with submitted/unscored predictions.
- Detect finished matches with predictions but zero scored predictions.
- Return non-zero exit code when inconsistencies are found.
- Add admin/API health visibility or compact log output if feasible.
- Add tests for the consistency checker.

### Out of scope
- No automatic destructive repair.
- No production data mutation.
- No UI dashboard redesign.
- No scoring rule changes.

### Acceptance criteria
- Command reports actionable inconsistencies.
- Command is safe to run in production.
- Tests cover each inconsistency.
- Admin/operator can use this after cron runs.

### Suggested commit message
Add finished match consistency checks

### Ticket ID
E20-T07

### Title
Add local and staging QA scenarios for knockout prediction and settlement

### Status
Todo

### Sprint
Post v1 hardening

### Priority
Critical

### Objective
Ensure login, prediction, API sync, settlement and leaderboard flows are covered before knockout production usage.

### Scope
- Update `docs/qa-checklist.md`.
- Define local QA scenarios for login, group prediction, knockout non-draw prediction, knockout draw + qualified-team prediction, closed prediction visibility, FT settlement, AET settlement, PEN settlement, and leaderboard update.
- Define staging QA procedure using controlled data or API snapshots.
- Ensure current Playwright suite is not run against production.
- Add focused tests where safe.

### Out of scope
- No production Playwright suite.
- No destructive production commands.
- No production data reset.

### Acceptance criteria
- QA checklist covers all critical flows.
- Staging validation can prove knockout scoring before real knockout matches.
- Login, prediction and settlement flows are explicitly tested.

### Suggested commit message
Add knockout settlement QA checklist

### Ticket ID
E20-T08

### Title
Run staging knockout QA and document release readiness

### Status
Todo

### Sprint
Post v1 hardening

### Priority
High

### Objective
Execute the staging QA process and document whether knockout scoring/UX is safe for production.

### Scope
- Deploy completed knockout changes to staging.
- Run Laravel tests and build locally.
- Run staging manual QA.
- Run relevant Playwright smoke tests if applicable.
- Run consistency checker after simulated or real finished fixtures.
- Document findings in `docs/qa-checklist.md` or release notes.

### Out of scope
- No new implementation unless QA finds a blocker.
- No production deploy until staging passes.

### Acceptance criteria
- Staging confirms knockout prediction UX works.
- Staging confirms FT/AET/PEN settlement works.
- Consistency checker reports no critical issues.
- Release readiness is documented before production deploy.

### Suggested commit message
Document knockout QA readiness
