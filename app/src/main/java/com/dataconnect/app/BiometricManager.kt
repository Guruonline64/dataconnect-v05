package com.dataconnect.app

import android.content.Context
import android.util.Base64
import androidx.biometric.BiometricManager as AndroidBiometricManager
import androidx.biometric.BiometricPrompt
import androidx.core.content.ContextCompat
import androidx.fragment.app.FragmentActivity
import android.security.keystore.KeyGenParameterSpec
import android.security.keystore.KeyProperties
import javax.crypto.Cipher
import javax.crypto.KeyGenerator
import javax.crypto.SecretKey
import javax.crypto.spec.GCMParameterSpec
import java.nio.charset.StandardCharsets

class BiometricManager(private val activity: FragmentActivity) {
    private val prefs = activity.getSharedPreferences("dataconnect_biometric", Context.MODE_PRIVATE)
    private val alias = "dataconnect_biometric_key"

    fun isAvailable(): Boolean = AndroidBiometricManager.from(activity).canAuthenticate(
        AndroidBiometricManager.Authenticators.BIOMETRIC_STRONG
    ) == AndroidBiometricManager.BIOMETRIC_SUCCESS

    fun isEnabled(): Boolean = prefs.contains("token") && prefs.contains("iv")

    fun enable(token: String, callback: (Boolean) -> Unit) {
        if (!isAvailable() || token.isBlank()) { callback(false); return }
        try {
            val cipher = Cipher.getInstance("AES/GCM/NoPadding")
            cipher.init(Cipher.ENCRYPT_MODE, getOrCreateKey())
            pendingEnable = token to callback
            val prompt = BiometricPrompt(activity, ContextCompat.getMainExecutor(activity), callbackFor({ _, _ -> }))
            prompt.authenticate(promptInfo("Enable fingerprint login", "Confirm your fingerprint to enable quick login"), BiometricPrompt.CryptoObject(cipher))
        } catch (_: Exception) { callback(false) }
    }

    fun disable() {
        prefs.edit().clear().apply()
        try {
            val ks = java.security.KeyStore.getInstance("AndroidKeyStore").apply { load(null) }
            if (ks.containsAlias(alias)) ks.deleteEntry(alias)
        } catch (_: Exception) {}
    }

    fun authenticate(callback: (Boolean, String?) -> Unit) {
        if (!isAvailable() || !isEnabled()) { callback(false, null); return }
        val cipher = try {
            Cipher.getInstance("AES/GCM/NoPadding").apply {
                init(Cipher.DECRYPT_MODE, getOrCreateKey(), GCMParameterSpec(128, Base64.decode(prefs.getString("iv", ""), Base64.NO_WRAP)))
            }
        } catch (_: Exception) { callback(false, null); return }
        val prompt = BiometricPrompt(activity, ContextCompat.getMainExecutor(activity), callbackFor(callback))
        prompt.authenticate(promptInfo("DataConnect fingerprint login", "Use your fingerprint to sign in securely"), BiometricPrompt.CryptoObject(cipher))
    }

    private var pendingEnable: Pair<String, (Boolean) -> Unit>? = null

    private fun callbackFor(callback: (Boolean, String?) -> Unit) = object : BiometricPrompt.AuthenticationCallback() {
        override fun onAuthenticationSucceeded(result: BiometricPrompt.AuthenticationResult) {
            val enable = pendingEnable
            if (enable != null) {
                pendingEnable = null
                try {
                    val cipher = result.cryptoObject?.cipher ?: throw IllegalStateException()
                    val encrypted = cipher.doFinal(enable.first.toByteArray(StandardCharsets.UTF_8))
                    prefs.edit().putString("token", Base64.encodeToString(encrypted, Base64.NO_WRAP)).putString("iv", Base64.encodeToString(cipher.iv, Base64.NO_WRAP)).apply()
                    enable.second(true)
                } catch (_: Exception) { enable.second(false) }
                return
            }
            try {
                val cipher = result.cryptoObject?.cipher ?: throw IllegalStateException()
                val encoded = prefs.getString("token", null) ?: throw IllegalStateException()
                val token = String(cipher.doFinal(Base64.decode(encoded, Base64.NO_WRAP)), StandardCharsets.UTF_8)
                callback(true, token)
            } catch (_: Exception) { callback(false, null) }
        }
        override fun onAuthenticationError(errorCode: Int, errString: CharSequence) {
            pendingEnable?.let { pendingEnable = null; it.second(false) }
            callback(false, null)
        }
    }

    private fun promptInfo(title: String, subtitle: String) = BiometricPrompt.PromptInfo.Builder().setTitle(title).setSubtitle(subtitle).setNegativeButtonText("Not now").build()

    private fun getOrCreateKey(): SecretKey {
        val ks = java.security.KeyStore.getInstance("AndroidKeyStore").apply { load(null) }
        (ks.getKey(alias, null) as? SecretKey)?.let { return it }
        val generator = KeyGenerator.getInstance(KeyProperties.KEY_ALGORITHM_AES, "AndroidKeyStore")
        generator.init(KeyGenParameterSpec.Builder(alias, KeyProperties.PURPOSE_ENCRYPT or KeyProperties.PURPOSE_DECRYPT)
            .setBlockModes(KeyProperties.BLOCK_MODE_GCM)
            .setEncryptionPaddings(KeyProperties.ENCRYPTION_PADDING_NONE)
            .setUserAuthenticationRequired(true)
            .build())
        return generator.generateKey()
    }
}
