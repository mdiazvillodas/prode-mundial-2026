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
Implemented with LeagueMembership, LeagueJoinRequest, league search by name/code, join requests, owner approval/rejection, owner auto-membership, member-only league detail access, duplicate request blocking, own-league request blocking, active-member blocking, and max 3 active private league memberships.

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
Implemented with invite route using visible league code, owner share/copy section, invitation page, reuse of existing join request flow, authenticated access, states for owner, active member, pending request, removed user, max 3 leagues, and new requester. Invitation links do not bypass owner approval.

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
  - max 3 active private league memberships

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
Todo

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
Todo

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
Todo

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
