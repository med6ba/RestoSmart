# RestoSmart Technical Notes

## Architecture

The central application owns SaaS concerns: super users, restaurant applications, tenants, plans, subscriptions, trial status, billing history, platform notifications, and tenant operating data.

Tenant routes live under `/{tenant}` and use `Stancl\Tenancy\Middleware\InitializeTenancyByPath` only to set the current tenant context. RestoSmart uses one central database; tenant-owned rows carry `tenant_id` and are scoped by the application models.

## Central Tables

- `users`: super, admin, kitchen, driver, and client accounts.
- `tenants`: tenant identity, slug, status, plan, admin contact, and trial/billing dates.
- `plans`: Starter, Pro, Business plan limits and feature lists.
- `restaurant_applications`: pending/approved/rejected onboarding requests.
- `subscriptions`: current plan and lifecycle status per tenant.
- `billing_histories`: billing-ready ledger entries.
- `platform_notifications`: central admin activity feed.

## Tenant-Scoped Tables

- `users`: tenant-scoped admin, kitchen, driver, and client rows via `tenant_id`.
- `categories`, `menu_items`: interactive menu.
- `ingredients`, `ingredient_menu_item`, `stock_movements`: stock tracking and low-stock alerts.
- `customer_addresses`: saved delivery addresses.
- `orders`, `order_items`: order lifecycle and line items.
- `deliveries`: driver assignment, route summary, pickup, and delivery confirmation.
- `notifications`: role/user alerts for admin, kitchen, driver, and client screens.

## Role Dashboards

- SaaS login (`/login`): accepts only `super` and restaurant `admin` accounts.
- Tenant login (`/{tenant}/login`): accepts only `kitchen`, `driver`, and `client` accounts for that restaurant.
- `admin`: restaurant KPIs, stock, staff, menu, order assignment.
- `kitchen`: KDS queue with delivery/click & collect distinction.
- `driver`: mobile-first dispatch queue and active route.
- `client`: menu, checkout, order history, live tracking.
- `super`: platform analytics, approvals, plans, tenant lifecycle.

## Order Status Flow

`received -> preparing -> ready -> assigned -> out_for_delivery -> delivered`

Click & collect orders stop at `ready` until handed over. Delivery orders can be assigned manually by admin or taken directly by a driver from the ready queue.

## Local Verification Notes

This machine has `pdo_sqlite.so` installed but not enabled in CLI PHP. I verified the SQLite fallback with:

```bash
php -d extension=pdo_sqlite artisan migrate:fresh --seed
php -d extension=pdo_sqlite vendor/bin/phpunit
npm run build
php -d extension=pdo_sqlite -S 0.0.0.0:9999 -t public public/index.php
```

If `pdo_sqlite` is enabled globally, the normal `php artisan migrate --seed` and `php artisan serve --host=0.0.0.0 --port=9999` commands work for the SQLite fallback. With MySQL configured, use the normal commands from the README.
