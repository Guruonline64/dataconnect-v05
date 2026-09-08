package com.dataconnect.app

import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.view.Window
import android.webkit.JavascriptInterface
import android.webkit.ValueCallback
import android.webkit.WebChromeClient
import android.webkit.WebResourceRequest
import android.webkit.WebView
import android.webkit.WebViewClient
import android.widget.Toast
import androidx.fragment.app.FragmentActivity
import androidx.webkit.WebViewAssetLoader

class MainActivity : FragmentActivity() {
    private lateinit var webView: WebView
    private lateinit var biometric: BiometricManager
    private var filePathCallback: ValueCallback<Array<Uri>>? = null
    private val fileChooserRequest = 1001

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        requestWindowFeature(Window.FEATURE_NO_TITLE)
        biometric = BiometricManager(this)
        webView = WebView(this)
        setContentView(webView)
        val assetLoader = WebViewAssetLoader.Builder().addPathHandler("/assets/", WebViewAssetLoader.AssetsPathHandler(this)).build()
        webView.settings.apply { javaScriptEnabled=true; domStorageEnabled=true; allowFileAccess=false; allowContentAccess=true; mediaPlaybackRequiresUserGesture=true; builtInZoomControls=false; displayZoomControls=false }
        webView.addJavascriptInterface(BiometricBridge(), "AndroidBiometric")
        webView.webViewClient = object : WebViewClient() {
            override fun shouldInterceptRequest(view: WebView, request: WebResourceRequest) = assetLoader.shouldInterceptRequest(request.url)
            override fun onReceivedError(view: WebView, request: WebResourceRequest, error: android.webkit.WebResourceError) { if(request.isForMainFrame) Toast.makeText(this@MainActivity,"Unable to load Data Connect",Toast.LENGTH_SHORT).show() }
        }
        webView.webChromeClient = object : WebChromeClient() {
            override fun onShowFileChooser(view: WebView?, callback: ValueCallback<Array<Uri>>?, params: FileChooserParams?): Boolean {
                filePathCallback?.onReceiveValue(null); filePathCallback=callback
                return try { startActivityForResult(params?.createIntent() ?: Intent(Intent.ACTION_GET_CONTENT).apply{type="image/*";addCategory(Intent.CATEGORY_OPENABLE)}, fileChooserRequest); true } catch(_:Exception){filePathCallback=null;false}
            }
        }
        webView.loadUrl("https://appassets.androidplatform.net/assets/index.html")
    }

    inner class BiometricBridge {
        @JavascriptInterface fun isAvailable(): Boolean = biometric.isAvailable()
        @JavascriptInterface fun isEnabled(): Boolean = biometric.isEnabled()
        @JavascriptInterface fun enable(token: String) { runOnUiThread { biometric.enable(token) { ok -> webView.post { webView.evaluateJavascript("window.onNativeBiometricEnabled(${ok.toString()});", null) } } } }
        @JavascriptInterface fun disable() { biometric.disable() }
        @JavascriptInterface fun authenticate() { runOnUiThread { biometric.authenticate { ok, token -> val js = "window.onNativeBiometricResult(${ok.toString()},${if(token==null)"null" else "'"+token.replace("\\","\\\\").replace("'","\\'")+"'"});"; webView.post{webView.evaluateJavascript(js,null)} } } }
    }
    @Deprecated("Deprecated in Java") override fun onActivityResult(requestCode:Int,resultCode:Int,data:Intent?){super.onActivityResult(requestCode,resultCode,data);if(requestCode==fileChooserRequest){val result=if(resultCode==RESULT_OK&&data?.data!=null)arrayOf(data.data!!)else null;filePathCallback?.onReceiveValue(result);filePathCallback=null}}
    @Deprecated("Deprecated in Java") override fun onBackPressed(){if(webView.canGoBack())webView.goBack()else super.onBackPressed()}
    override fun onDestroy(){filePathCallback?.onReceiveValue(null);filePathCallback=null;webView.stopLoading();webView.destroy();super.onDestroy()}
}
