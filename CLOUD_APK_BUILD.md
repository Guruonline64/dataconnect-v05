# Build the APK without Android Studio

This project includes a GitHub Actions workflow that builds the debug APK in the cloud.

## Steps

1. Create a GitHub account if you do not already have one.
2. Create a new repository, for example `dataconnect-v05`.
3. Upload the contents of this project to the repository (the files inside `DataConnect_V05_AndroidStudio`, not the outer folder).
4. Commit to the `main` branch.
5. Open the repository's **Actions** tab.
6. Select **Build Data Connect APK**.
7. Click **Run workflow** if it is not already running.
8. Open the completed workflow run.
9. Under **Artifacts**, download `DataConnect-V05-debug-apk`.
10. Extract the downloaded artifact and install `app-debug.apk` on an Android phone.

The APK is a V05 progress/demo build. It does not contain VTU.ng credentials and it does not make real purchases until the backend is deployed and the provider integration is configured.
