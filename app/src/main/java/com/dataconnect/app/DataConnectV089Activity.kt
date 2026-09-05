package com.dataconnect.app

import android.app.Activity
import android.os.Bundle
import android.widget.TextView
import android.widget.Button
import android.widget.Toast
import org.json.JSONObject

class DataConnectV089Activity : Activity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_main)

        findViewById<Button>(R.id.dataCenterButton).setOnClickListener { toast("Data Center") }
        findViewById<Button>(R.id.airtimeButton).setOnClickListener { toast("Airtime") }
        findViewById<Button>(R.id.walletButton).setOnClickListener { toast("Wallet") }
        findViewById<Button>(R.id.sharesButton).setOnClickListener { toast("Shares") }
        findViewById<Button>(R.id.withdrawalButton).setOnClickListener { toast("Withdrawal") }
        findViewById<Button>(R.id.accountButton).setOnClickListener { toast("Account") }
        findViewById<Button>(R.id.customerCareButton).setOnClickListener { toast("Customer Care") }

        refreshDashboard()
    }

    private fun refreshDashboard() {
        Thread {
            val r = runCatching { DataConnectV087Controller.refreshDashboard() }.getOrNull()
            runOnUiThread {
                if (r?.ok == true) {
                    val s = DataConnectV087Session.state
                    findViewById<TextView>(R.id.walletBalance).text =
                        "Wallet balance: ₦" + String.format("%.2f", s.walletBalance)
                    findViewById<TextView>(R.id.shareCount).text = "Active shares: ${s.activeShares}"
                    findViewById<TextView>(R.id.notificationCount).text =
                        "Unread notifications: ${s.unreadNotifications}"
                }
            }
        }.start()
    }

    private fun toast(message: String) =
        Toast.makeText(this, message, Toast.LENGTH_SHORT).show()
}
