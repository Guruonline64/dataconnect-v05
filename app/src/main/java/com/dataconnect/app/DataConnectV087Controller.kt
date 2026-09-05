package com.dataconnect.app

import org.json.JSONObject

/**
 * Screen integration facade for V08.7.
 * UI screens call these methods; backend remains the source of truth.
 */
object DataConnectV087Controller {
    fun login(phone: String, password: String): DataConnectApiClient.Result {
        DataConnectV087Session.setLoading(true)
        val r=DataConnectApiService.login(phone,password)
        if(r.ok) {
            val body=runCatching{JSONObject(r.body)}.getOrNull()
            val token=body?.optString("token",null)
            DataConnectV087Session.setLoggedIn(true, token)
        } else DataConnectV087Session.fail("Login failed")
        return r
    }

    fun refreshDashboard(): DataConnectApiClient.Result {
        val r=DataConnectApiService.dashboard()
        if(r.ok) {
            val d=runCatching{JSONObject(r.body).optJSONObject("data")}.getOrNull()
            DataConnectV087Session.updateDashboard(
                d?.optDouble("wallet_balance",0.0) ?: 0.0,
                d?.optInt("active_shares",0) ?: 0,
                d?.optInt("unread_notifications",0) ?: 0
            )
        } else DataConnectV087Session.fail("Could not load dashboard")
        return r
    }

    fun loadDataPlans() = DataConnectApiService.dataPlans()
    fun loadWallet() = DataConnectApiService.wallet()
    fun loadTransactions() = DataConnectApiService.transactions()
    fun loadNotifications() = DataConnectApiService.notifications()
    fun loadShares() = DataConnectApiService.shares()
    fun loadHoldings() = DataConnectApiService.holdings()
    fun loadShareReturns() = DataConnectApiService.shareReturns()
    fun loadWithdrawals() = DataConnectApiService.withdrawals()
}
