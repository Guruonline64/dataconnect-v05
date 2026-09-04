# Data Connect V05 — Android Studio Project

This project packages the existing Data Connect V05 frontend into a native Android WebView shell for progress/demo review.

## Current status
- Includes the V05 frontend screens and styling.
- Runs offline from Android app assets.
- Does NOT embed PHP/MySQL.
- Does NOT expose VTU.ng credentials.
- Existing V05 demo interactions remain available for UI review.

## Open in Android Studio
1. Install Android Studio.
2. File → Open.
3. Select this project folder (`DataConnect_V05_AndroidStudio`).
4. Let Gradle sync.
5. Build → Build APK(s).
6. Android Studio will place the debug APK under `app/build/outputs/apk/debug/`.

Android Studio/Gradle is required to compile the APK. The Android SDK and JDK must be installed on the build machine.

## Connecting the real backend later
The web frontend currently defaults to `http://localhost:8000` in `frontend/js/api.js` for the V05 local backend. For a phone APK, the backend must be deployed to an HTTPS URL. Change the API base URL to the deployed HTTPS backend before enabling real authentication, wallet operations, and VTU.ng purchases.

Never place the VTU.ng secret/token in the Android app. It must remain on the PHP backend.


## Cloud APK build

If Android Studio cannot be installed, see `CLOUD_APK_BUILD.md`. A GitHub Actions workflow is included to build the APK remotely.
