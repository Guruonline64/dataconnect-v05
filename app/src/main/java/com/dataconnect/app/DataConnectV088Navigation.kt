package com.dataconnect.app

/**
 * V08.8 navigation contract.
 * Existing Activity/Fragment/Compose code can map these destinations to its UI.
 */
enum class DataConnectScreen {
    LOGIN,
    REGISTER,
    DASHBOARD,
    DATA_CENTER,
    AIRTIME,
    WALLET,
    TRANSACTIONS,
    SHARES,
    WITHDRAWAL,
    ACCOUNT,
    MARKETER,
    NOTIFICATIONS,
    STAFF_DASHBOARD,
    STAFF_AIRTIME,
    STAFF_WITHDRAWALS,
    STAFF_MARKETERS,
    SIC_CHAT,
    CUSTOMER_CARE
}

object DataConnectNavigation {
    var current: DataConnectScreen = DataConnectScreen.LOGIN
        private set

    fun go(screen: DataConnectScreen) { current = screen }

    fun afterLogin(isStaff: Boolean) {
        current = if (isStaff) DataConnectScreen.STAFF_DASHBOARD else DataConnectScreen.DASHBOARD
    }

    fun logout() { current = DataConnectScreen.LOGIN }
}
