# Data Connect V07.2 backend deployment

When the company provides hosting:
1. Create a MySQL database and user.
2. Import `database/schema.sql`.
3. Upload `backend/api` and `backend/config` to the server.
4. Configure environment variables from `.env.example` in the hosting environment.
5. Enable HTTPS.
6. Test `GET /api/health`.
7. Set the Android app's Backend Connection URL to `https://YOUR-DOMAIN/api`.

Never place MySQL or VTU credentials in the Android assets or JavaScript.
