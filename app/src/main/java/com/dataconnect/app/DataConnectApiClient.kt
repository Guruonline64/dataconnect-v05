package com.dataconnect.app

import org.json.JSONObject
import java.net.HttpURLConnection
import java.net.URL

/**
 * Small API client for Data Connect V08.6.
 * Set BASE_URL to the company's backend URL at deployment time.
 * Never put VTU.ng credentials here.
 */
object DataConnectApiClient {
    // Development placeholder. Replace with the company's HTTPS backend URL before testing against a server.
    var BASE_URL: String = "https://YOUR-DATACONNECT-DOMAIN.example"

    var authToken: String? = null

    data class Result(val ok: Boolean, val code: Int, val body: String)

    fun request(path: String, method: String = "GET", json: JSONObject? = null): Result {
        val conn = (URL(BASE_URL.trimEnd('/') + path).openConnection() as HttpURLConnection)
        conn.requestMethod = method
        conn.connectTimeout = 15000
        conn.readTimeout = 20000
        conn.setRequestProperty("Accept", "application/json")
        conn.setRequestProperty("Content-Type", "application/json")
        authToken?.let { conn.setRequestProperty("Authorization", "Bearer ${it}") }
        if (json != null) {
            conn.doOutput = true
            conn.outputStream.use { it.write(json.toString().toByteArray(Charsets.UTF_8)) }
        }
        return try {
            val code = conn.responseCode
            val stream = if (code in 200..299) conn.inputStream else conn.errorStream
            val body = stream?.bufferedReader()?.use { it.readText() } ?: ""
            Result(code in 200..299, code, body)
        } finally { conn.disconnect() }
    }
}
