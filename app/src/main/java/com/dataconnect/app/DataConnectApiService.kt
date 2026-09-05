package com.dataconnect.app

import org.json.JSONObject

object DataConnectApiService {
    fun login(phone: String, password: String) =
        DataConnectApiClient.request("/api/login.php", "POST",
            JSONObject().put("phone", phone).put("password", password))

    fun register(phone: String, username: String, password: String) =
        DataConnectApiClient.request("/api/register.php", "POST",
            JSONObject().put("phone", phone).put("username", username).put("password", password))

    fun dashboard() = DataConnectApiClient.request(DataConnectApiRoutes.DASHBOARD)
    fun dataPlans() = DataConnectApiClient.request(DataConnectApiRoutes.DATA_PLANS)
    fun wallet() = DataConnectApiClient.request(DataConnectApiRoutes.WALLET)
    fun transactions() = DataConnectApiClient.request(DataConnectApiRoutes.TRANSACTIONS)
    fun notifications() = DataConnectApiClient.request(DataConnectApiRoutes.NOTIFICATIONS)
    fun shares() = DataConnectApiClient.request(DataConnectApiRoutes.SHARES)
    fun holdings() = DataConnectApiClient.request(DataConnectApiRoutes.HOLDINGS)
    fun shareReturns() = DataConnectApiClient.request(DataConnectApiRoutes.SHARE_RETURNS)
    fun withdrawals() = DataConnectApiClient.request(DataConnectApiRoutes.WITHDRAWALS)
}
