package com.dataconnect.app

import org.json.JSONObject

/**
 * V09.0 core customer screen actions.
 * Every financial action is sent to the server and should be followed by a refresh.
 */
object DataConnectV090ScreenController {
    fun loadDashboard() = DataConnectV087Controller.refreshDashboard()
    fun loadDataCenter() = DataConnectApiService.dataPlans()
    fun loadWallet() = DataConnectApiService.wallet()
    fun loadTransactions() = DataConnectApiService.transactions()
    fun loadShares() = DataConnectApiService.shares()
    fun loadHoldings() = DataConnectApiService.holdings()
    fun loadShareReturns() = DataConnectApiService.shareReturns()
    fun loadWithdrawals() = DataConnectApiService.withdrawals()
    fun requestWithdrawal(amount: Int) = DataConnectV088Actions.requestWithdrawal(amount)
    fun buyShare(packageId: Int) = DataConnectV088Actions.buyShare(packageId)
    fun requestAirtime(network: String, amount: Double, phone: String) =
        DataConnectApiClient.request("/api/request-airtime.php","POST",
            JSONObject().put("network",network).put("amount",amount).put("recipient_phone",phone))
}
