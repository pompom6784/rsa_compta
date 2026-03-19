# RSA Compta

An accounting application that ingests XLS/CSV files from multiple sources, stores data in per-year SQLite databases, and produces Excel exports matching the original format.

**Key technologies:** PHP 8.3+ · Laravel 12 (Eloquent ORM, Artisan) · SQLite (one database file per accounting year) · PhpSpreadsheet

---

## Requirements

- PHP 8.3 or newer
- Composer
- Docker (optional, for the containerised setup)

---

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
```

---

## Database setup

Each accounting year uses its own SQLite file located at `var/db_YYYY.sqlite`.

### Create a new year database

```bash
# Create the SQLite file for a given year (e.g. 2024)
touch var/db_2024.sqlite
```

### Run migrations on all year databases

The custom `db:migrate-all` command applies (or rolls back) Artisan migrations against **every** `var/db_YYYY.sqlite` file automatically:

```bash
# Apply all pending migrations to every year database
php artisan db:migrate-all

# Roll back the last migration batch on every year database
php artisan db:migrate-all --rollback

# Force execution in production (skips the confirmation prompt)
php artisan db:migrate-all --force
```

> If you only want to migrate the currently-selected year you can use the
> standard Artisan command instead:
> ```bash
> php artisan migrate
> ```

---

## Running the application

### Local development server

```bash
composer start
# or equivalently
php artisan serve
```

The application will be available at <http://localhost:8000>.

### Docker

```bash
docker-compose up -d
```

The application will be available at <http://localhost:8090>.

---

## Running the test suite

```bash
composer test
```

---

## Static analysis

```bash
composer phpstan
```

