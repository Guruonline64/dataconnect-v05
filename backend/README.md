# Data Connect — Laravel 13 Backend Migration

This backend replaces the legacy PHP endpoint implementation while **leaving the Android/Web frontend untouched**.

## Compatibility guarantee

The frontend files under `app/src/main/...` were not modified. The Laravel API intentionally supports the existing routes, including the `.php` suffix:

- `/api/login.php`
- `/api/register.php`
- `/api/me.php`
- `/api/wallet.php`
- `/api/transactions.php`
- `/api/notifications.php`
- `/api/dashboard.php`
- `/api/data-plans.php`
- `/api/purchase-data.php`
- `/api/process-data-order.php`
- `/api/refund-data.php`
- `/api/airtime-requests.php`
- `/api/request-airtime.php`
- `/api/share-packages.php`
- `/api/share-holdings.php`
- `/api/share-returns.php`
- `/api/buy-share.php`
- `/api/withdrawals.php`
- `/api/withdrawal-request.php`
- `/api/marketer-apply.php`
- staff/dispenser endpoints
- `/api/post-daily-share-returns.php`

Bearer-token authentication is preserved at the HTTP contract level, so the existing frontend does not need a UI or API-client rewrite.

## Install

```bash
cd backend
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

Configure MySQL and the provider values in `.env`, then serve the `backend/public` directory.

For Apache/Nginx, the document root should be:

```text
DataConnect_V07_2_AndroidStudio/backend/public
```

Do not expose `storage`, `.env`, or the project root directly.

## Daily returns

Run the scheduler:

```bash
php artisan schedule:work
```

Production schedulers can invoke:

```bash
php artisan schedule:run
```

The scheduled job posts at 00:05 server time.

## VTU provider

The old implementation deliberately did not guess the production VTU.ng API contract. The Laravel adapter therefore requires:

```text
VTU_BASE_URL=
VTU_TOKEN=
VTU_DATA_PATH=
```

Only put provider credentials on the server.

## Legacy implementation

The original PHP backend has been retained in:

```text
backend_php_legacy/
```

It is not used by the Laravel application.
