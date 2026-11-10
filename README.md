## 1. Requirements

- PHP 8.3 or newer with `pdo_mysql`, `fileinfo`, `openssl`, `mbstring`, `tokenizer`, `xml`, and `ctype` enabled.
- Composer 2.
- Node.js 20.19+ or 22.12+ and npm. Vite 8 requires one of these Node versions.
- MySQL 8+ or a compatible MariaDB release.

Check the installed tools:

```bash
php -v
composer --version
node --version
npm --version
```

## 2. Install the project packages

Run those commands from the repository root.

```bash
composer install
npm ci
```

`composer install` installs the PHP packages recorded in `composer.lock`, including Laravel Framework and Laravel Tinker. `npm ci` installs the exact frontend packages recorded in `package-lock.json`:

| Package | Purpose |
| --- | --- |
| Laravel Framework `^13.17` | Server-side application, sessions, database access, validation, and JSON endpoints |
| Vue `^3.5` | Application screens and interactive forms |
| Vue Router `^4.6` | Customer, admin, owner, and login pages in the browser |
| Bootstrap `^5.3` and Popper `^2.11` | UI styles, responsive layout, and Bootstrap components |
| Axios `^1.21` | Requests to Laravel's JSON endpoints using the session |
| Vite `^8`, `laravel-vite-plugin`, and `@vitejs/plugin-vue` | Frontend development server, production build, and Vue component compilation |
| Tailwind CSS `^4` and `@tailwindcss/vite` | Processing the project's Tailwind CSS setup |

These dependencies are declared in `composer.json` and `package.json`. Use `composer install` and `npm ci` for a fresh clone; they keep the versions consistent with the lockfiles.

The frontend plugins are wired in `vite.config.js`. Vue and Vue Router start from `resources/js/app.js`, Axios and the session CSRF header are set up in `resources/js/bootstrap.js`, and Bootstrap/Tailwind styles are in `resources/css/app.css`.

## 3. Create the MySQL database

Start MySQL, then create an empty database with UTF-8 support. Run this in a MySQL client:

```sql
CREATE DATABASE shopsuite
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

The MySQL user configured below must be allowed to create and modify tables in this database.

## 4. Configure the environment

Copy `.env.example` to `.env`.

PowerShell:

```powershell
Copy-Item .env.example .env
```

macOS/Linux:

```bash
cp .env.example .env
```

Set these values in `.env`, using your local MySQL username and password:

```dotenv
APP_NAME=ShopSuite
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=shopsuite
DB_USERNAME=root
DB_PASSWORD=
```

The example environment uses database-backed sessions, cache, and queues. Their tables are included in the migrations. Keep `.env` private; it contains local secrets and should not be committed.

## 5. Generate the key, migrate, and seed

```bash
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

The migrations create users, shops, catalog products, shop listings, and the Laravel support tables. The seeder creates sample admin and owner accounts, shops, shared products, and shop listings. The storage link makes uploaded shop thumbnails available to the browser.

The migration files add the user/authentication tables, user roles, shops, shared products, and the `shop_products` join table. Laravel's cache and queue tables are included for the database-backed drivers configured in `.env.example`. `DatabaseSeeder` uses stable demo emails and product SKUs, so running the seeder again updates its sample records instead of duplicating them.

## 6. Start the application

Use two terminals from the repository root.

Terminal 1 — Laravel:

```bash
php artisan serve
```

Terminal 2 — Vite development server:

```bash
npm run dev
```

Open <http://localhost:8000>. Vite will recompile Vue and CSS changes while the development server is running.

To build production frontend assets instead, run:

```bash
npm run build
```

Then serve the Laravel application with your normal PHP web server. The built frontend assets are read from `public/build`.

## Demo accounts

The seeder creates these local demo accounts. They all use the password `password`:

| Role | Email |
| --- | --- |
| Admin | `admin@example.com` |
| Shop owner | `owner1@example.com` |
| Shop owner | `owner2@example.com` |

Sign in at <http://localhost:8000/login>. These credentials are for local development only; change or remove them before deployment.

## Main pages

| Page | URL | Access |
| --- | --- | --- |
| Customer shop directory | `/` | Public |
| Customer shop page | `/shops/{shop-slug}` | Public; only active shops and listings are shown |
| Sign in | `/login` | Guests |
| Admin dashboard | `/admin` | Admin role |
| Admin shops | `/admin/shops` | Admin role |
| Admin product catalog | `/admin/products` | Admin role |
| Shop-owner accounts | `/admin/owners` | Admin role |
| Owner dashboard | `/owner` | Shop-owner role |
| Owner shop products | `/owner/shops/{shop-id}/products` | That shop's owner |

Admin and owner add/edit forms use Vue dialogs. Laravel middleware and controller checks enforce roles and shop ownership on the server. The Vue client sends requests to Laravel's JSON endpoints using the same-origin session cookie.

## Catalog and thumbnails

- `products` is the shared catalog. Admins and shop owners can create catalog products.
- `shop_products` connects a catalog product to a shop and stores that shop's price, description, thumbnail path, and active status.
- A catalog product is visible to customers only when it has an active listing in an active shop.
- The same catalog product can have different prices and listing details in different shops.
- Shop thumbnails are stored under `storage/app/public/shop-thumbnails` and served through the `public/storage` link.

Apply future migrations with:

```bash
php artisan migrate
```

Re-run the demo seeder without dropping tables:

```bash
php artisan db:seed
```

To reset the database and restore only demo data:

```bash
php artisan migrate:fresh --seed
```

**Warning:** `migrate:fresh` drops every table in the configured MySQL database. Do not use it if you need to keep existing data.

## Tests

Run the automated test suite with:

```bash
php artisan test
```
