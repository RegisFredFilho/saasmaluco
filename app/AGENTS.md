# SindiGig — guidelines for agents

This repository is a Laravel 13 application evolved from SaaSMaluco.

## Official local runtime

Use **Docker Compose** from the repository root (`../` relative to this file, i.e. `sindigig/`).

Do **not** require host PHP/XAMPP for day-to-day work. Host PHP 8.0 (typical XAMPP) is incompatible (`composer.json` requires `^8.3`).

```powershell
# from repository root
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app php artisan test
```

Full setup: `docs/LOCAL_SETUP.md` and `scripts/setup.ps1`.

## Product docs (read before domain changes)

- `docs/PROJECT_OVERVIEW.md`
- `docs/ARCHITECTURE.md`
- `docs/ROADMAP.md`
- `docs/BUSINESS_RULES.md`

## Cursor rules

Project rules live in `.cursor/rules/`. Follow them for architecture, security, database, business rules, tests, and local Docker usage.

## Optional: Laravel Boost

Laravel Boost may be installed later inside the app container if the team wants framework-specific agent tools. It is **not** required to complete MVP stages.

```sh
docker compose exec app composer require laravel/boost --dev
docker compose exec app php artisan boost:install
```
