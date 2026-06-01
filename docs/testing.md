# Local Testing Setup

The local test suite uses MySQL, not SQLite. This avoids requiring the SQLite PHP driver locally and keeps tests aligned with the development database engine.

## Database

Create a dedicated local testing database:

```sql
CREATE DATABASE prode_mundial_2026_testing;
```

The normal development database remains:

```text
prode_mundial_2026
```

## Environment

`phpunit.xml` sets the test connection to:

```text
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=prode_mundial_2026_testing
```

Do not commit real database passwords. If your local MySQL user needs a password, create a local `.env.testing` from `.env.testing.example` and set `DB_USERNAME` / `DB_PASSWORD` there.

`.env.testing` is ignored by Git.

## Run Tests

```bash
php artisan test
```

To run only the scoring tests:

```bash
php artisan test --filter=PredictionScoringServiceTest
```
