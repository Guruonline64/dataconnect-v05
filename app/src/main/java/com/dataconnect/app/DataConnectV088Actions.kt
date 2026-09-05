package com.dataconnect.app

import org.json.JSONObject

object DataConnectV088Actions {
    fun requestWithdrawal(amount: Int) =
        DataConnectApiClient.request("/api/withdrawal-request.php", "POST",
            JSONObject().put("amount", amount))

    fun buyShare(packageId: Int) =
        DataConnectApiClient.request("/api/buy-share.php", "POST",
            JSONObject().put("package_id", packageId))

    fun airtimeHistory() =
        DataConnectApiClient.request(DataConnectApiRoutes.AIRTIME_REQUESTS)
}
