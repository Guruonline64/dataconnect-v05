# Data Connect V13 — Admin & Staff Control Center

V13 adds a responsive browser-based control center backed by the existing PHP API.

## Setup
1. Deploy `backend_php_legacy` to an HTTPS PHP/MySQL server.
2. Run `database/migration_v13.sql`.
3. Edit `admin/app.js` and set `API` to your HTTPS API URL, ending in `/api`.
4. Open `admin/index.html` in a browser or serve the `admin` directory from your domain.
5. Sign in with an existing `admin` or `staff` account.

## Admin-only actions
- Wallet credit/debit adjustments
- Add/toggle data plans

## Staff/admin actions
- Dashboard metrics
- User list/search
- Pending withdrawals and decisions
- Pending airtime requests
- Marketer list
- Data-plan viewing

Do not deploy with `JWT_SECRET=CHANGE_ME`; use a strong server-side secret.
