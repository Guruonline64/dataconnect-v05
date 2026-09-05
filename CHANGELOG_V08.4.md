# Data Connect V08.4

Added:
- Share package and holding APIs.
- Server-side daily share-return posting with duplicate-day protection.
- Staff withdrawal queue.
- Staff withdrawal approval/rejection with wallet locking and ledger entry.
- Staff marketer queue and approval/rejection.
- Audit logging for privileged financial actions.

Daily returns are posted by a controlled server/admin job, not by the Android client.
Production deployment still requires security hardening, HTTPS, proper session/token infrastructure, database backups, monitoring and confirmed VTU.ng API documentation.
