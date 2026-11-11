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
