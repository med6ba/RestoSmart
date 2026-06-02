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

## Smart Delivery Chat

Smart Delivery Chat lets a client and the delivery driver assigned to that client's order coordinate in real time with Laravel Reverb, Broadcasting, Echo, and Alpine.js.

- Visible only to tenant `client` and `driver` users as **Delivery Chat** in the desktop sidebar and mobile bottom nav.
- Client routes: `/{tenant}/client/delivery-chat`, `/{tenant}/client/delivery-chat/{order}`, `/{tenant}/client/delivery-chat/{order}/send`.
- Driver routes: `/{tenant}/driver/delivery-chat`, `/{tenant}/driver/delivery-chat/{order}`, `/{tenant}/driver/delivery-chat/{order}/send`.
- Clients can access only their own delivery orders after a driver is assigned.
- Drivers can access only delivery orders assigned to them.
- Admin, super admin, and kitchen users cannot access chat routes or subscribe to chat channels.
- Chat is available while the order is assigned or out for delivery. Once delivered, the thread stays visible but the composer is locked with: `Chat is closed because this order has already been delivered.`
- Messages are tenant-owned rows in `delivery_messages` and are not deleted when delivery completes.

Local Reverb workflow:

```bash
php artisan serve --host=0.0.0.0 --port=9999
php artisan reverb:start
npm run dev
```

The default `.env.example` includes local Reverb values:

```dotenv
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=local-app-id
REVERB_APP_KEY=local-app-key
REVERB_APP_SECRET=local-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

See [docs/TECHNICAL.md](docs/TECHNICAL.md) for data dictionary, architecture notes, and evaluation coverage.
