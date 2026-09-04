package com.dataconnect.app

import android.annotation.SuppressLint
import android.app.Activity
import android.os.Bundle
import android.webkit.WebChromeClient
import android.webkit.WebResourceError
import android.webkit.WebResourceRequest
import android.webkit.WebSettings
import android.webkit.WebView
import android.webkit.WebViewClient
import java.io.IOException

class MainActivity : Activity() {
    private lateinit var webView: WebView

    @SuppressLint("SetJavaScriptEnabled")
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        webView = WebView(this)
        setContentView(webView)

        webView.settings.apply {
            javaScriptEnabled = true
            domStorageEnabled = true
            databaseEnabled = true
            allowFileAccess = true
            allowContentAccess = true
            javaScriptCanOpenWindowsAutomatically = false
            mixedContentMode = WebSettings.MIXED_CONTENT_COMPATIBILITY_MODE
        }

        webView.webViewClient = object : WebViewClient() {
            override fun shouldOverrideUrlLoading(view: WebView, request: WebResourceRequest): Boolean = false

            override fun onReceivedError(
                view: WebView,
                request: WebResourceRequest,
                error: WebResourceError
            ) {
                super.onReceivedError(view, request, error)
            }
        }
        webView.webChromeClient = WebChromeClient()

        loadApp()
    }

    private fun loadApp() {
        try {
            val html = assets.open("index.html").bufferedReader(Charsets.UTF_8).use { it.readText() }
            // Use the asset directory as the document base URL so relative css/js/manifest
            // references resolve correctly inside Android WebView.
            webView.loadDataWithBaseURL(
                "file:///android_asset/",
                html,
                "text/html",
                "UTF-8",
                null
            )
        } catch (_: IOException) {
            webView.loadData(
                "<html><body style=\"font-family:sans-serif;padding:24px\"><h2>Data Connect</h2><p>Unable to load the app assets.</p></body></html>",
                "text/html",
                "UTF-8"
            )
        }
    }

    override fun onBackPressed() {
        if (webView.canGoBack()) webView.goBack() else super.onBackPressed()
    }

    override fun onDestroy() {
        webView.stopLoading()
        webView.destroy()
        super.onDestroy()
    }
}
