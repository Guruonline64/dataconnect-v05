# Laravel deployment checklist

1. Point the web server document root to `backend/public`.
2. Copy `.env.example` to `.env`.
3. Set the MySQL database credentials.
4. Set a strong `APP_KEY` with `php artisan key:generate`.
5. Set `APP_URL`.
6. Keep `VTU_BASE_URL`, `VTU_TOKEN`, and `VTU_DATA_PATH` server-side.
7. Run `composer install --no-dev --optimize-autoloader`.
8. Run `php artisan migrate`.
9. Run `php artisan optimize`.
10. Run `php artisan schedule:work` during development, or configure cron in production:
   `php artisan schedule:run` every minute.
11. Confirm `/api/health.php` returns JSON success.

The Android/Web frontend does not need a UI change because the existing `.php` API paths remain available.
