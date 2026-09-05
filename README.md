# Data Connect V07 — Backend Ready
Smart Way to Buy Data

V07 adds a real PHP + MySQL authentication foundation.

Backend:
- database/schema.sql
- api/index.php
- api/.htaccess
- config/database.php
- .env.example

Deploy the backend over HTTPS, create the MySQL database with schema.sql, set DB_HOST/DB_NAME/DB_USER/DB_PASSWORD/JWT_SECRET on the server, then in the app use Account → Backend Connection.

The Android app never contains VTU credentials. The current API endpoints are registration, login, profile, wallet read, transactions read, and notifications read.

Next implementation: wallet funding/debit ledger endpoints, data plans/orders, server-side VTU.ng integration, dispenser authorization, withdrawals, marketer approvals, and staff chat.
