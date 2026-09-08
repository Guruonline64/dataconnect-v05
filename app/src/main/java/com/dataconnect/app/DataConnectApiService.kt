package com.dataconnect.app

import org.json.JSONObject

object DataConnectApiService {
    fun login(phone: String, password: String) =
        DataConnectApiClient.request(DataConnectApiRoutes.LOGIN, "POST",
            JSONObject().put("email", phone).put("password", password))

    fun register(name: String, email: String, phone: String, password: String, passwordConfirmation: String = password) =
        DataConnectApiClient.request(DataConnectApiRoutes.REGISTER, "POST",
            JSONObject().put("phone", phone).put("name", name).put("email", email).put("password", password).put("password_confirmation", passwordConfirmation))

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
