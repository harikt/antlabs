# README

## Requirements

* PHP 8.4 +
* Symfony CLI
* MySql

## Setup 

Clone the repo, 

```sh
composer install
```

Create .env.local with DATABASE_URL for more infoamtion @see .env

```sh
bin/console doctrine:database:create
bin/console doctrine:migrations:migrate
```

## Running Server

```sh
symfony server:start
```

Example shows symfony cli, but you can use nginx, or even php's built in server.

## Creating a Task

```sh
curl -si -X POST "http://localhost:8000/api/tasks" -H 'Content-Type: application/json' -H 'Accept: application/json' \
  -d '{"title":"Renew ASP licence","description":"Q1 batch","due_date":"2027-12-15"}'
```

## List Tasks

```sh
curl -si "http://localhost:8000/api/tasks"
```

```sh
curl -si -G "http://localhost:8000/api/tasks" \
--data-urlencode 'status=todo' \
--data-urlencode 'due_before=2026-12-31'
```

## # Create task with invalid title and past due_date -> 422
```sh
curl -si -X POST "http://localhost:8000/api/tasks" -H 'Content-Type: application/json' -H 'Accept: application/json' \
  -d '{"title":"ab","due_date":"2020-01-01"}'
```

# Update status

```sh
curl -si -X PATCH "http://localhost:8000/api/tasks/1/status" -H 'Content-Type: application/json' -H 'Accept: application/json' \
  -d '{"status":"done"}'
```

## Using Rest Client

`request.http` contains the routes which can be executed if [Rest Client](https://marketplace.visualstudio.com/items?itemName=humao.rest-client) is installed


## Tests

Setup the test db

```sh
bin/console doctrine:database:create --env=test
bin/console doctrine:migrations:migrate --env=test -n
```

## Run the unit tests

```sh
bin/phpunit
```

Note : 

- Timezones: due_date is stored as given, without converting to UTC.
