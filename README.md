# E-commerce Store (Laravel 13)

An Arabic/English online store with a customer storefront, an admin dashboard, and a JSON API.

## Features

- **Storefront:** product catalog with filters, simple and variable products, cart (guest and signed-in), checkout, order history, favorites, reviews, and a blog with comments.
- **Dashboard:** products, categories, brands, attributes, tags, coupons, governorates (shipping prices), orders, posts, users, and roles with permissions.
- **API:** public catalog and token-protected management and cart endpoints (Laravel Sanctum).
- **Site settings:** site name, logo, contact details, social links, tax, and payment methods, under Site Settings in the dashboard.
- **Notifications:** order notifications saved to the database and broadcast through Pusher.

Customers can pay with cash on delivery or a simulated test card. Test card payments move no money, take no real card details, and are always off when `APP_ENV=production`. A real payment provider is not connected yet.

## Requirements

PHP 8.4+, Composer, Node.js 20+, and MySQL 8.

## Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Set the `DB_*` values in `.env` and create that database, then:

```bash
php artisan migrate --seed
php artisan storage:link
composer dev
```

The store opens at http://localhost:8000 and the dashboard at `/dashboard`.

The seeder creates `admin@gmail.com` and `user@gmail.com`, both with the password `password`. Use them for local development only.

## Scheduled tasks

Add `php artisan schedule:run` to cron so that pending orders older than 30 days are removed weekly and their stock is returned.

## Tests

Tests run against a separate MySQL database so they can never touch your development data. Create it once:

```sql
CREATE DATABASE example_app_testing CHARACTER SET utf8mb4;
```

Then run:

```bash
php artisan test
```

`tests/TestCase.php` refuses to run unless the database name ends with `_testing`.

## Permissions

Users get their access from roles, and the `admin` role can do everything. `config/permissions.php` lists every permission. After adding one, run:

```bash
php artisan db:seed --class=RolePermissionSeeder
```

## API

```
POST /api/login        { "email": "...", "password": "..." } returns a token
```

Send the token as `Authorization: Bearer <token>`. `GET /api/products` and `GET /api/categories` are public. Everything else in `routes/api.php` needs a token.
