plugins {
    id("com.android.application")
    id("org.jetbrains.kotlin.android")
}

android {
    namespace = "com.dataconnect.app"
    compileSdk = 36

    defaultConfig {
        applicationId = "com.dataconnect.app"
        minSdk = 24
        targetSdk = 36
        versionCode = 5
        versionName = "0.5.0"
    }

    buildTypes {
        release {
            isMinifyEnabled = false
            isShrinkResources = false
        }
    }
}
