# RestoSmart

RestoSmart is a Laravel SaaS platform for distance ordering, kitchen preparation, delivery dispatch, stock tracking, and restaurant tenant management.

## Stack

- Laravel 13, compatible with the requested Laravel 12+ target
- Blade, TailwindCSS, Alpine.js
- MySQL for production-like deployment
- `stancl/tenancy` for path-based tenant context, with all data stored in the central database
- Laravel Breeze authentication scaffolding

## Quick Start

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve --host=0.0.0.0 --port=9999
```

Open `http://localhost:9999`.

The default `.env.example` targets MySQL. For machines where MySQL is not configured but `pdo_sqlite` is available, switch `DB_CONNECTION=sqlite`, set `DB_DATABASE` to an absolute SQLite path, then run the same migration command.

## Seeded Accounts

All seeded accounts use the password `password`.

- Super: `super@restosmart.com`
- Admin: `admin@demo.com`
- Kitchen: `kitchen@demo.com`
- Driver: `driver@demo.com`
- Client: `client@demo.com`

Demo restaurant URL: `/demo`

Clients access a restaurant from its tenant URL, for example `/{tenant-slug}`. The menu is public, and clients can create an account or log in from that restaurant page before checkout.

SaaS login (`/login`) is for Super and Admin accounts. Tenant login (`/{tenant-slug}/login`) is for Kitchen, Driver, and Client accounts only.

## Main Workflows

- Super approves/rejects restaurant applications, manages plans, updates tenant lifecycle statuses, and monitors SaaS analytics.
- Restaurant admin manages menu, stock, staff, order dispatch, and manual driver assignment.
- Client browses the menu, manages a cart, checks out for delivery or click & collect, and tracks order status with Alpine polling.
- Kitchen receives orders, starts preparation, and marks orders ready.
- Driver sees assigned and ready delivery orders, picks up, follows a simulated route, and validates delivery.

See [docs/TECHNICAL.md](docs/TECHNICAL.md) for data dictionary, architecture notes, and evaluation coverage.
