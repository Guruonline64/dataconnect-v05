package com.dataconnect.app

data class DataConnectV087State(
    val loading: Boolean = false,
    val loggedIn: Boolean = false,
    val walletBalance: Double = 0.0,
    val activeShares: Int = 0,
    val unreadNotifications: Int = 0,
    val error: String? = null
)

object DataConnectV087Session {
    var token: String? = null
    var state: DataConnectV087State = DataConnectV087State()
        private set

    fun setLoading(value: Boolean) { state = state.copy(loading = value) }
    fun setLoggedIn(value: Boolean, newToken: String? = token) {
        token = newToken
        state = state.copy(loggedIn = value)
        DataConnectApiClient.authToken = newToken
    }
    fun updateDashboard(balance: Double, shares: Int, unread: Int) {
        state = state.copy(loading=false, walletBalance=balance, activeShares=shares, unreadNotifications=unread, error=null)
    }
    fun fail(message: String) { state = state.copy(loading=false, error=message) }
}
