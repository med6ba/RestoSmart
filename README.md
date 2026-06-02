# RestoSmart

RestoSmart is a multi-tenant Laravel SaaS platform for restaurants that need online ordering, kitchen operations, stock tracking, delivery dispatch, table QR ordering, billing-ready subscriptions, and role-based dashboards in one application.

It is built as a full restaurant workflow demo: platform owners can onboard restaurants, restaurant admins can manage operations, clients can place orders, kitchen staff can prepare them, and drivers can complete deliveries with real-time chat support.

## Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Architecture](#architecture)
- [Requirements](#requirements)
- [Installation](#installation)
- [Environment Configuration](#environment-configuration)
- [Running the App](#running-the-app)
- [Demo Data](#demo-data)
- [User Roles](#user-roles)
- [Core Workflows](#core-workflows)
- [Real-Time Delivery Chat](#real-time-delivery-chat)
- [RestoBot AI Assistant](#restobot-ai-assistant)
- [Testing](#testing)
- [Project Structure](#project-structure)
- [Deployment Notes](#deployment-notes)
- [Documentation](#documentation)
- [License](#license)

## Overview

RestoSmart models both sides of a restaurant SaaS product:

- The platform layer handles restaurant applications, tenant lifecycle, plans, subscriptions, billing history, platform notifications, super-admin analytics, and user impersonation.
- The tenant layer handles restaurant menus, carts, checkout, table orders, kitchen queues, delivery dispatch, driver workflows, customer order tracking, stock movements, receipts, and delivery chat.

Tenants are accessed through path-based URLs such as `/demo`, `/medina`, or `/atlas`. The application uses one central database, while tenant-owned records are scoped with `tenant_id`.

## Features

### SaaS Platform

- Restaurant application form and approval workflow.
- Super-admin dashboard for tenant analytics and operational visibility.
- Plan management with Starter, Pro, and Business tiers.
- Tenant lifecycle management for active, trial, and suspended restaurants.
- Subscription and billing-history records prepared for payment integration.
- Platform notification feed.
- User management by role.
- Super-admin impersonation for support/debugging workflows.

### Restaurant Operations

- Public tenant menu page.
- Tenant-specific login and registration.
- Role-based dashboards for admin, kitchen, driver, and client users.
- Category and menu item management.
- Staff invitation for restaurant teams.
- Stock tracking by ingredient and stock movement history.
- Low-stock visibility.
- Restaurant table management with QR tokens.
- Local table ordering, takeaway/click-and-collect, and delivery checkout.
- Order receipts generated as PDF responses.
- Kitchen display workflow for received, preparing, ready, and collected orders.
- Manual delivery assignment by restaurant admin.
- Driver dispatch queue, pickup flow, delivery confirmation, and route metadata.
- Client order history and live status polling.
- Tenant and role notifications.

### Customer Experience

- Browse restaurant menus without logging in.
- Add, update, and clear cart items.
- Register/login from the restaurant tenant page.
- Checkout for delivery, takeaway, or local table ordering.
- Save delivery address details during checkout.
- Track order status after placement.
- Download/view receipt PDF.
- Chat with the assigned delivery driver while the order is active.

### Internationalization

RestoSmart includes language files for:

- English
- French
- Spanish
- Arabic

The locale switcher is available through the application UI and uses Laravel localization files under `lang/`.

## Tech Stack

- PHP `^8.3`
- Laravel `^13.8`
- Laravel Breeze authentication scaffolding
- Laravel Reverb for WebSocket broadcasting
- Laravel Echo and Pusher JS client
- `stancl/tenancy` for path-based tenant context
- Blade templates
- Tailwind CSS
- Alpine.js
- Vite
- MySQL for production-like local development
- SQLite fallback for lightweight local testing when `pdo_sqlite` is enabled
- Groq API integration for RestoBot
- PHPUnit `^12`
- Laravel Pint

## Architecture

RestoSmart uses path-based tenancy:

```text
/                  Platform landing page
/login             SaaS login for super/admin users
/dashboard         Platform dashboard
/{tenant}          Public restaurant menu
/{tenant}/login    Tenant login for kitchen/driver/client users
/{tenant}/admin    Restaurant admin dashboard
/{tenant}/kitchen  Kitchen display system
/{tenant}/driver   Driver dispatch dashboard
```

The app uses `Stancl\Tenancy\Middleware\InitializeTenancyByPath` to set the current tenant from the URL path. Unlike separate-database tenancy, this project keeps data in the central database and scopes tenant-owned models with `tenant_id`.

Important domain services:

- `PlatformProvisioningService` provisions restaurants from approved applications.
- `CartService` manages tenant cart state.
- `OrderWorkflowService` places orders, updates statuses, creates delivery records, writes stock movements, and sends notifications.
- `RestoBotService` connects restaurant admins to the Groq chat completion API.

## Requirements

- PHP 8.3 or newer
- Composer
- Node.js and npm
- MySQL 8+ recommended
- SQLite optional for local fallback
- PHP extensions commonly required by Laravel, including `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, and either `pdo_mysql` or `pdo_sqlite`
- Optional: Groq API key for RestoBot

## Installation

Clone the repository and install dependencies:

```bash
git clone https://github.com/med6ba/RestoSmart.git
cd RestoSmart

composer install
npm install
cp .env.example .env
php artisan key:generate
```

Configure your database in `.env`, then run migrations and seeders:

```bash
php artisan migrate --seed
```

Build frontend assets:

```bash
npm run build
```

## Environment Configuration

The default `.env.example` is configured for MySQL:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=restosmart
DB_USERNAME=root
DB_PASSWORD=
```

For SQLite local development, enable `pdo_sqlite`, create a database file, and update `.env`:

```bash
touch database/database.sqlite
```

```dotenv
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/RestoSmart/database/database.sqlite
```

The application also includes local Reverb values:

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

For RestoBot, add:

```dotenv
GROQ_API_KEY=your-groq-api-key
GROQ_MODEL=llama-3.1-8b-instant
```

## Running the App

Run the Laravel server:

```bash
php artisan serve --host=0.0.0.0 --port=9999
```

Open:

```text
http://localhost:9999
```

For frontend development:

```bash
npm run dev
```

For the full local development stack, the Composer script starts Laravel, the queue listener, Reverb, logs, and Vite together:

```bash
composer run dev
```

You can also run Reverb manually:

```bash
php artisan reverb:start
```

## Demo Data

Seeders create platform data, subscription plans, demo restaurants, staff users, clients, menu items, ingredients, stock movements, table QR tokens, orders, deliveries, messages, billing records, and notifications.

All seeded accounts use:

```text
password
```

### Platform Account

| Role | Email | Login |
| --- | --- | --- |
| Super Admin | `super@restosmart.com` | `/login` |

### Demo Tenant Accounts

| Tenant | URL | Admin | Kitchen | Driver | Client |
| --- | --- | --- | --- | --- | --- |
| Demo Restaurant | `/demo` | `admin@demo.com` | `kitchen@demo.com` | `driver@demo.com` | `client@demo.com` |
| Medina Bistro | `/medina` | `admin@medina.test` | `kitchen@medina.test` | `driver@medina.test` | `client@medina.test` |
| Atlas Kitchen | `/atlas` | `admin@atlas.test` | `kitchen@atlas.test` | `driver@atlas.test` | `client@atlas.test` |
| Ocean Grill | `/ocean` | `admin@ocean.test` | `kitchen@ocean.test` | `driver@ocean.test` | `client@ocean.test` |

Important login rules:

- SaaS login at `/login` accepts super users and restaurant admins.
- Tenant login at `/{tenant}/login` accepts kitchen, driver, and client users.
- Public menus are available at `/{tenant}` without authentication.

## User Roles

| Role | Scope | Main Capabilities |
| --- | --- | --- |
| Super | Platform | Manage applications, plans, tenants, payments, platform users, notifications, and impersonation |
| Admin | Tenant restaurant owner/manager | Manage menu, categories, stock, tables, staff, orders, driver assignment, and RestoBot |
| Kitchen | Tenant operations | View kitchen queue, start preparation, mark orders ready, mark local/takeaway orders collected, adjust stock |
| Driver | Tenant delivery | View assigned/ready delivery orders, pick up orders, validate delivery, chat with clients |
| Client | Tenant customer | Browse menu, manage cart, checkout, track orders, download receipts, chat with driver |

## Core Workflows

### Restaurant Onboarding

1. A restaurant applies through `/restaurant/apply`.
2. A super admin reviews the application.
3. Approval provisions a tenant, subscription, billing records, admin account, and platform notification.
4. The restaurant becomes available at its tenant URL.

### Ordering

1. Client opens a tenant menu, for example `/demo`.
2. Client adds menu items to the cart.
3. Client logs in or registers from the tenant page.
4. Client checks out as delivery, takeaway, or local table order.
5. The order enters the kitchen queue with status `received`.
6. Stock movements are generated from menu item ingredient requirements.

### Kitchen

```text
received -> preparing -> ready
```

Kitchen staff can mark local/takeaway orders as collected after they are ready.

### Delivery

```text
received -> preparing -> ready -> assigned -> out_for_delivery -> delivered
```

Delivery orders can be assigned manually by an admin or handled through the driver workflow once ready.

### Table QR Ordering

Restaurant admins can create tables and download/generate QR codes. Clients scanning a table token can place local orders linked to the table. Occupied tables are protected from duplicate active orders until the existing order is collected or closed.

## Real-Time Delivery Chat

RestoSmart includes a Smart Delivery Chat powered by Laravel Reverb, Broadcasting, Echo, and Alpine.js.

Chat access rules:

- Only tenant `client` and `driver` users can access delivery chat routes.
- Clients can only open their own delivery orders after a driver is assigned.
- Drivers can only open orders assigned to them.
- Super, admin, and kitchen users cannot access delivery chat channels.
- Delivered orders remain readable, but the message composer is locked.
- Messages are stored in the `delivery_messages` table and remain available after delivery completion.

Main routes:

```text
/{tenant}/client/delivery-chat
/{tenant}/client/delivery-chat/{order}
/{tenant}/client/delivery-chat/{order}/send

/{tenant}/driver/delivery-chat
/{tenant}/driver/delivery-chat/{order}
/{tenant}/driver/delivery-chat/{order}/send
```

## RestoBot AI Assistant

RestoBot helps restaurant admins create dish ideas, recipes, ingredient lists, preparation steps, menu descriptions, kitchen notes, allergens, and pricing ideas.

It is intentionally scoped to restaurant/menu work. If a prompt is unrelated to dishes or recipes, the service returns a short refusal.

Enable it by adding a Groq API key to `.env`:

```dotenv
GROQ_API_KEY=your-groq-api-key
GROQ_MODEL=llama-3.1-8b-instant
```

Admin route:

```text
/{tenant}/admin/restobot
```

## Testing

Run the test suite:

```bash
composer test
```

Or run Laravel's test runner directly:

```bash
php artisan test
```

The feature tests cover authentication, profile updates, tenant workflows, demo seeders, platform user management, and delivery chat authorization/workflows.

Frontend production build:

```bash
npm run build
```

Laravel Pint formatting:

```bash
vendor/bin/pint
```

## Project Structure

```text
app/
  Events/                 Broadcast events for notifications and delivery chat
  Http/Controllers/       Platform, auth, and tenant controllers
  Http/Middleware/        Role and locale middleware
  Http/Requests/          Form request validation
  Models/                 Platform and tenant domain models
  Policies/               Authorization policies
  Services/               Platform provisioning, cart, order workflow, RestoBot
  Support/                Money formatting, QR generation, receipt PDF generation

database/
  migrations/             Platform, tenancy, restaurant, delivery, and table schemas
  seeders/                Platform plans, super user, demo tenants, demo operations data

resources/
  views/                  Blade pages, layouts, tenant dashboards, and components
  css/                    Tailwind entrypoint
  js/                     Alpine/Echo frontend entrypoint

routes/
  web.php                 Platform routes
  tenant.php              Path-based tenant routes
  auth.php                Breeze authentication routes
  channels.php            Broadcast channel authorization

tests/
  Feature/                End-to-end application workflows
  Unit/                   Unit test area
```

## Deployment Notes

Before deploying:

- Set `APP_ENV=production` and `APP_DEBUG=false`.
- Configure a production database and run `php artisan migrate --force`.
- Use a queue driver appropriate for production if background jobs are added.
- Configure Reverb/WebSocket infrastructure if real-time delivery chat is enabled.
- Configure mail credentials for real emails.
- Set `GROQ_API_KEY` only if RestoBot should be enabled.
- Run `npm run build` and serve the generated Vite assets.
- Run Laravel optimization commands as appropriate for your environment:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Documentation

More technical details are available in:

- [docs/TECHNICAL.md](docs/TECHNICAL.md)

That document includes architecture notes, table descriptions, role dashboards, order status flow, and local verification notes.

## License

RestoSmart is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
