# Biznova API

Backend-only Laravel API organized as a modular monolith. Business capabilities live in `app/Features`; each feature owns its models, HTTP code and migrations. All HTTP routes are registered in the single `routes/api.php` file and use the `/api/v1` prefix.

## Setup

```bash
composer install
cp .env.example .env
```

Configure PostgreSQL in `.env`, then run:

```bash
php artisan key:generate
php artisan migrate --seed
composer run dev
```

The API listens on `http://127.0.0.1:8000`. Health check: `GET /api/v1/health`.
Run automated tests with `composer test`.

## Company dashboard

Authenticated company owners can retrieve annual financial aggregates with:

```text
GET /api/v1/companies/{company}/dashboard?year=2026
```

The `year` parameter is optional (defaults to the current year). The response contains income, expenses and net totals, twelve monthly trends, category and tag breakdowns, and monthly budget comparisons. All records are scoped to the requested company.

## Docker deployment

The Docker Compose setup runs the API with PHP-FPM, Nginx and PostgreSQL. Create the root `.env` file, set a production `APP_KEY` and a strong `DB_PASSWORD`, then start the services and apply migrations explicitly:

```bash
cp .env.example .env
php artisan key:generate
# Set APP_ENV=production, APP_DEBUG=false, APP_URL=http://localhost:8080,
# DB_USERNAME=biznova, and a strong DB_PASSWORD in .env.
docker compose up -d --build
docker compose exec app php artisan migrate --force
```

The API is available at `http://localhost:8080` by default; set `APP_PORT` to change the host port. PostgreSQL data is persisted in the `postgres_data` volume. Configure TLS, backups and production secrets management before exposing the service publicly. Migrations are not run automatically during container startup.

## Features

```text
app/Features/
├── Identity/        # User model and token authentication
├── ReferenceData/   # Sectors and financial categories
├── ProjectAnalysis/ # Forecast projects, inputs and reports
├── BusinessFinance/ # Companies, transactions, budgets and reports
└── System/          # API health check
```

Each feature keeps its migrations in its own `Database/Migrations` directory. `FeatureServiceProvider` discovers and registers these migrations. Route declarations stay centralized so the API has one route source of truth.

## Authentication

The API uses Laravel Sanctum. Registration and login return an access token; send it on protected requests as `Authorization: Bearer <token>`. Tokens expire after seven days and logout revokes the token used for the request.

Endpoints:

- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout` (authenticated)
- `GET /api/v1/auth/profile` (authenticated)
- `PATCH /api/v1/auth/profile` (authenticated)
