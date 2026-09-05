package com.dataconnect.app

data class DataPlan(
    val id: Int,
    val network: String,
    val name: String,
    val price: Double,
    val dataAmount: String,
    val validityDays: Int
)

data class SharePackage(
    val id: Int,
    val name: String,
    val investmentAmount: Double,
    val dailyReturn: Double,
    val durationDays: Int
)

data class WithdrawalOption(val amount: Int)

object DataConnectV090Options {
    val withdrawals = listOf(500,1000,2000,5000,10000).map(::WithdrawalOption)
}
