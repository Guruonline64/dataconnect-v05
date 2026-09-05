package com.dataconnect.app

import android.app.Activity
import android.os.Bundle
import android.widget.TextView

class DataConnectV090CoreActivity : Activity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        val screen = intent.getStringExtra("screen") ?: "dashboard"
        when(screen) {
            "data" -> setContentView(R.layout.screen_data_center)
            "airtime" -> setContentView(R.layout.screen_airtime)
            "wallet" -> setContentView(R.layout.screen_wallet)
            "shares" -> setContentView(R.layout.screen_shares)
            "withdrawal" -> setContentView(R.layout.screen_withdrawal)
            "account" -> setContentView(R.layout.screen_account)
            else -> setContentView(R.layout.activity_main)
        }
        findViewById<TextView>(R.id.screenStatus)?.text =
            "Connect this screen to the Data Connect test API to load live information."
    }
}
