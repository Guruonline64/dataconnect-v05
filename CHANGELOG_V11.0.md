# Changelog — V11.0

## Backend connection
- Added strict HTTPS URL validation.
- Accepts either a domain root or a pasted `/api` URL and normalizes it.
- Added backend health testing in the app.
- Added request timeout handling.
- Clears both supported token storage keys on logout/401.

## Authentication
- Registration now logs the user in immediately after account creation.
- Login stores the API token and refreshes user/wallet data.

## Data Center
- Confirm Purchase now calls the live PHP data-order endpoint when a backend is configured.
- Sends network, plan name, amount and recipient phone in the format expected by the backend.
- Refreshes the wallet after the order request.
- Shows the returned reference and status.

## Bug fixes
- Fixed accidental literal `\\n` characters in `app.js` that prevented JavaScript parsing.
- Fixed the marketer application PHP parenthesis error.
