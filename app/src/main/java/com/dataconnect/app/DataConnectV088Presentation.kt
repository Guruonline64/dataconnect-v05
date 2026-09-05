package com.dataconnect.app

data class DataConnectMenuItem(
    val screen: DataConnectScreen,
    val title: String
)

object DataConnectV088Presentation {
    const val APP_NAME = "Data connect"
    const val SLOGAN = "Smart way to buy data"
    const val PRIMARY_COLOR = "#1565C0"
    const val ACCENT_COLOR = "#FFFFFF"

    val customerMenu = listOf(
        DataConnectMenuItem(DataConnectScreen.DASHBOARD, "Dashboard"),
        DataConnectMenuItem(DataConnectScreen.DATA_CENTER, "Data Center"),
        DataConnectMenuItem(DataConnectScreen.AIRTIME, "Airtime"),
        DataConnectMenuItem(DataConnectScreen.WALLET, "Wallet"),
        DataConnectMenuItem(DataConnectScreen.TRANSACTIONS, "Transactions"),
        DataConnectMenuItem(DataConnectScreen.SHARES, "Shares"),
        DataConnectMenuItem(DataConnectScreen.WITHDRAWAL, "Withdrawal"),
        DataConnectMenuItem(DataConnectScreen.ACCOUNT, "Account"),
        DataConnectMenuItem(DataConnectScreen.NOTIFICATIONS, "Notifications"),
        DataConnectMenuItem(DataConnectScreen.MARKETER, "Marketer"),
        DataConnectMenuItem(DataConnectScreen.SIC_CHAT, "SIC Staff Chat"),
        DataConnectMenuItem(DataConnectScreen.CUSTOMER_CARE, "Customer Care")
    )

    val staffMenu = listOf(
        DataConnectMenuItem(DataConnectScreen.STAFF_DASHBOARD, "Staff Dashboard"),
        DataConnectMenuItem(DataConnectScreen.STAFF_AIRTIME, "Airtime Requests"),
        DataConnectMenuItem(DataConnectScreen.STAFF_WITHDRAWALS, "Withdrawal Requests"),
        DataConnectMenuItem(DataConnectScreen.STAFF_MARKETERS, "Marketers"),
        DataConnectMenuItem(DataConnectScreen.SIC_CHAT, "SIC Staff Chat"),
        DataConnectMenuItem(DataConnectScreen.ACCOUNT, "Account")
    )
}
