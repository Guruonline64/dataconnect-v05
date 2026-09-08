<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

$endpoints = [
    'health', 'register', 'login', 'me', 'wallet', 'transactions', 'notifications',
    'dashboard', 'data-plans', 'purchase-data', 'process-data-order', 'refund-data',
    'airtime-requests', 'request-airtime', 'share-packages', 'share-holdings',
    'share-returns', 'buy-share', 'withdrawals', 'withdrawal-request',
    'marketer-apply', 'staff-dashboard', 'staff-airtime-requests',
    'staff-airtime-approve', 'staff-airtime-reject', 'staff-withdrawals',
    'staff-withdrawal-decision', 'staff-marketers', 'staff-marketer-decision',
    'post-daily-share-returns',
];

foreach ($endpoints as $endpoint) {
    Route::match(['GET','POST','OPTIONS'], "/{$endpoint}.php", [ApiController::class, 'dispatch'])
        ->defaults('endpoint', $endpoint);
    Route::match(['GET','POST','OPTIONS'], "/{$endpoint}", [ApiController::class, 'dispatch'])
        ->defaults('endpoint', $endpoint);
}


// Data Connect V13.6.1 password reset endpoints.
// Production implementation must send the reset code through a verified provider
// and must never return the code in the API response.
Route::post('/auth/forgot-password', [PasswordResetController::class, 'forgot']);
Route::post('/auth/reset-password', [PasswordResetController::class, 'reset']);
