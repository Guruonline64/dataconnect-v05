# Laravel migration completed

- Frontend/interface files were preserved unchanged.
- Legacy PHP backend moved to `backend_php_legacy/`.
- New Laravel 13 backend is in `backend/`.
- Legacy `/api/*.php` URLs remain supported by Laravel routes.
- Configure `backend/.env`, run Composer, then `php artisan migrate`.
- VTU credentials belong only in server `.env`.
