# Task Management API

Mini JSON API built with Symfony 7.4 + Doctrine ORM 3 on MySQL.

| Method | Path | Description |
|---|---|---|
| `POST` | `/api/tasks` | Create a task → `201` |
| `GET` | `/api/tasks?status=&due_before=` | List tasks (filters optional), sorted by `due_date ASC, created_at DESC` |
| `PATCH` | `/api/tasks/{id}/status` | Change status → `200`, `404` unknown id, `422` invalid status |

Errors always look like `{ "errors": { "field": ["message"] } }`.

## Setup

Requirements: PHP 8.2+, Composer, MySQL 8+.

```bash
composer install

# Point Doctrine at your MySQL (this file is git-ignored)
echo 'DATABASE_URL="mysql://root:password@127.0.0.1:3306/antlabs?serverVersion=8.0.32&charset=utf8mb4"' > .env.local

php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate --no-interaction
```

## Run

```bash
symfony server:start -d          # or: php -S 127.0.0.1:8000 -t public
```

Tests (uses a separate `<dbname>_test` database):

```bash
cp .env.local .env.test.local
php bin/console doctrine:database:create --env=test --if-not-exists
php bin/console doctrine:schema:create --env=test
php bin/phpunit
```

## Example requests

`symfony server:start` serves **HTTPS** and answers `http://` with a `307` redirect, so always use `https://`.
macOS `/usr/bin/curl` trusts the Symfony CA from the Keychain (`symfony server:ca:install`).
A Homebrew/OpenSSL curl does not, and fails with `SSL certificate problem`. For that one, pass the CA explicitly:

```bash
export CURL_CA_BUNDLE="$HOME/Library/Application Support/symfony-cli/certs/rootCA.pem"
```

Create a task:

```bash
curl -i -X POST https://127.0.0.1:8000/api/tasks \
  -H 'Content-Type: application/json' \
  -d '{"title":"Prepare demo","description":"Captive portal walkthrough","status":"in_progress","due_date":"2026-12-01T10:00:00+05:30"}'
```

List todo tasks due before a date:

```bash
curl -i 'https://127.0.0.1:8000/api/tasks?status=todo&due_before=2027-01-01T00:00:00Z'
```

Update status:

```bash
curl -i -X PATCH https://127.0.0.1:8000/api/tasks/1/status \
  -H 'Content-Type: application/json' \
  -d '{"status":"done"}'
```

Validation error example (`422`):

```json
{
  "errors": {
    "title": ["This value is too short. It should have 3 characters or more."],
    "status": ["Status must be one of: todo, in_progress, done."],
    "due_date": ["Due date must be in the future."]
  }
}
```

## Design

```
Controller (thin: map request → call service → return JSON)
  ├─ DTOs + #[MapRequestPayload]/#[MapQueryString]  validation
  ├─ TaskService                                    business logic
  │    └─ TaskRepository::findByFilters()           QueryBuilder, no raw SQL
  └─ ApiExceptionListener                           one JSON error format
```

- `src/Entity/Task.php` – `tasks` table; `Status` is a PHP backed enum.
- `src/Dto/*` – `CreateTaskRequest`, `UpdateTaskStatusRequest`, `TaskFilter`.
- `src/Service/TaskService.php` – create / list / changeStatus.
- `src/EventListener/ApiExceptionListener.php` – maps validation (422), not found (404) and other HTTP errors to JSON.
- `src/Migrations/Version20261030143205.php` – creates the `tasks` table.
- JSON keys are snake_case via the serializer's `camel_case_to_snake_case` name converter (`config/packages/serializer.yaml`).
- Dates are stored in UTC and returned as RFC 3339.
- MySQL sorts `NULL` first in `ASC`, so tasks without a `due_date` appear at the top of the list.
