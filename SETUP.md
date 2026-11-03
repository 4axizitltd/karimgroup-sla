# sla.4axiz.com setup (Laravel API + Next.js admin)

These files are meant to be copied into new projects on the 4axiz server.
They need PHP 8.2+, Composer, Node 18+ and MySQL. None of these are installed on the karimgroup server.

## 1. Laravel API (`laravel/`)
```bash
composer create-project laravel/laravel sla-api
cd sla-api
php artisan install:api            # installs Sanctum + routes/api.php
```
Copy everything from `laravel/` into the project. Overwrite `routes/api.php`.

Add the Sanctum trait to `app/Models/User.php`:
```php
use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable { use HasApiTokens, HasFactory, Notifiable; ... }
```

Copy the private key. It is SECRET, so keep it out of git:
```bash
mkdir -p storage/app/sla
cp /path/to/private.pem storage/app/sla/private.pem   # from 4axiz_SLA\keys\private.pem
chmod 600 storage/app/sla/private.pem
```

Set up `.env` (DB_*, APP_URL=https://sla.4axiz.com), then run:
```bash
php artisan migrate
php artisan db:seed --class=SlaSeeder     # prints admin email + password once
```
Point `sla.4axiz.com` at `public/` and use HTTPS.

CORS: run `php artisan config:publish cors`, then set `allowed_origins` to the admin panel's URL.

## 2. Next.js admin (`nextjs/`)
```bash
cd nextjs
cp .env.local.example .env.local         # NEXT_PUBLIC_API_URL=https://sla.4axiz.com/api
npm install
npm run build && npm start               # port 3001
```
The pages can also be merged into the existing 4axiz.com Next.js project under `/admin/sla`. Copy `app/*`, `components/*` and `lib/api.ts`.

## 3. Turn on online mode in karimgroup
In `karimgroup/application/config/sla.php`:
```php
$config['sla_online_enabled'] = TRUE;
```

## Daily use
1. Client pays. Open Admin, go to **Clients → Karim Group → Record payment & generate code**.
2. Copy the code (for example `KG-7F3A-92BC-11DE`) or the ready-made message, and send it to the client.
3. The client enters it at **Setting → SLA Activation**. Reports open at once.
4. If the client already activated once, the next code gets picked up automatically on the daily check after payment. Entering it is optional.
5. **Cancel** a code to lock reports at the client's next online check (within about a day).
6. If the client moves to a new server, use **Reset installation** and the code can be entered again there.

## Endpoints
| Method | URL | Used by |
|---|---|---|
| POST | /api/activate | client software |
| POST | /api/status | client software (daily) |
| POST | /api/admin/login | admin |
| GET | /api/admin/dashboard | admin |
| GET/POST/PUT | /api/admin/clients | admin |
| GET/POST/PUT | /api/admin/applications | admin |
| POST/PATCH | /api/admin/subscriptions, /{id}/reset-install, /{id}/suggest-period | admin |
| GET/POST | /api/admin/payments | admin (POST also generates the code) |
| GET | /api/admin/codes, POST /codes/{id}/revoke, GET /codes/{id}/offline-key | admin |
| GET | /api/admin/logs | admin |

## Live server notes (sla.4axiz.com, 64.227.142.141)
- Laravel lives in `/var/www/sla.4axiz.com/api`, the admin in `/var/www/sla.4axiz.com/admin` (Next.js, systemd `sla-admin`, port 3010). Config files are in `deploy/`.
- Two things `install:api` did not do and must be done by hand:
  1. `bootstrap/app.php`: add `api: __DIR__.'/../routes/api.php',` inside `withRouting(...)`.
  2. `app/Models/User.php`: `use Laravel\Sanctum\HasApiTokens;` and `use HasApiTokens, HasFactory, Notifiable;`.
- `api/public/.htaccess` (standard Laravel rewrite file) is needed because Apache serves `public/` directly.
- `.env`: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://sla.4axiz.com`, MySQL database `sla` with its own user (not root).
- This server also hosts the Biddaniketon platform. Its catch-all vhost answers any domain that has no vhost of its own, so a new domain needs its own :80 and :443 vhost (certbot) before it works.
- Admin panel update: upload `nextjs/app` and `nextjs/components` to `admin/`, run `npm run build` there as www-data, then `systemctl restart sla-admin`.
