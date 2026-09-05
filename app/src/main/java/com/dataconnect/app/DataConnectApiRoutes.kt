package com.dataconnect.app

/**
 * V08.5 customer/staff API route registry.
 * Keep the base URL configurable for development vs company production hosting.
 */
object DataConnectApiRoutes {
    const val DASHBOARD = "/api/dashboard.php"
    const val DATA_PLANS = "/api/data-plans.php"
    const val PURCHASE_DATA = "/api/purchase-data.php"
    const val AIRTIME_REQUESTS = "/api/airtime-requests.php"
    const val REQUEST_AIRTIME = "/api/request-airtime.php"
    const val WALLET = "/api/wallet.php"
    const val TRANSACTIONS = "/api/transactions.php"
    const val NOTIFICATIONS = "/api/notifications.php"
    const val SHARES = "/api/share-packages.php"
    const val HOLDINGS = "/api/share-holdings.php"
    const val SHARE_RETURNS = "/api/share-returns.php"
    const val WITHDRAWALS = "/api/withdrawals.php"
    const val WITHDRAWAL_REQUEST = "/api/withdrawal-request.php"
    const val STAFF_DASHBOARD = "/api/staff-dashboard.php"
}
