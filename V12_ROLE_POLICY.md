# Data Connect V12 Role Policy

Ordinary customer accounts must only see customer services. Marketer, Staff/SIC and Dispenser services are conditionally displayed only when the authenticated backend role grants that role.

The Android/web UI also guards these routes, but this is defense-in-depth: the Laravel backend must independently authorize every privileged endpoint.

Password visibility: Login and Create Account use the same password control with a Show/Hide button.
