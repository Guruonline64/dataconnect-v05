# Data Connect V06.2

This version is the app + backend-ready development package.

## Android
The Android app is a WebView wrapper around the bundled Data Connect frontend.

## Backend
`backend/database/schema.sql` contains the MySQL schema for:
- users/authentication
- wallets and wallet ledger
- data orders
- airtime dispenser approvals
- share packages/holdings
- shareholder withdrawals
- marketer profiles
- notifications
- staff messages

Copy `backend/.env.example` to your server environment and fill in your own secrets there.
Never put VTU credentials in the Android frontend.

## Next implementation stage
1. Deploy PHP backend over HTTPS.
2. Create MySQL database using schema.sql.
3. Implement secure session/JWT authentication.
4. Connect wallet ledger with database transactions.
5. Connect VTU.ng server-side.
6. Add dispenser/staff authorization and working-hours rules.
7. Connect notifications and real withdrawal approvals.

## V07.3 API contract

The Android client expects these endpoints under the configured HTTPS API base URL:

- `POST /api/register.php`
- `POST /api/login.php`
- `GET /api/me.php`
- `GET /api/wallet.php`
- `GET /api/transactions.php`
- `GET /api/notifications.php`
- `GET /api/health.php`

Until the company supplies hosting, the Android app remains usable in demo mode. Configure the production API URL only after the PHP/MySQL server is deployed over HTTPS.

## V07.4 workflow endpoints to implement

- `GET /api/staff/airtime-requests.php`
- `POST /api/staff/airtime-approve.php`
- `POST /api/staff/airtime-reject.php`
- `GET /api/marketer.php`
- `POST /api/marketer/apply.php`
- `POST /api/withdrawals.php`

Staff endpoints must enforce role permissions server-side; the Android UI is never a security boundary.

## V08 implemented API

The following endpoints are now implemented as a development baseline:

- `POST /api/register.php`
- `POST /api/login.php`
- `GET /api/me.php`
- `GET /api/wallet.php`
- `GET /api/transactions.php`
- `GET /api/notifications.php`
- `GET /api/health.php`

### Production note
The token implementation here is a development placeholder. Before production deployment, replace it with a robust signed token/session system, strict CORS, HTTPS-only transport, rate limiting, CSRF protection where applicable, audit logging, and server-side role/permission checks.

## V08.1 data transaction engine

Implemented:
- HMAC-signed development authentication tokens
- Atomic wallet balance locking/debit
- Wallet ledger entry for data purchases
- Data order creation
- Failed-order refund endpoint
- Transaction rollback on errors

The actual VTU.ng provider call is intentionally separate and will be added only after the company supplies the production server environment.
