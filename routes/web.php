<?php

use App\Http\Controllers\CashierTransactionController;
use App\Http\Controllers\MarketingDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()->role === 'kasir') return redirect('/kasir');
        if (auth()->user()->role === 'marketing') return redirect('/marketing');
    }
    return redirect('/login');
});

// Grup Route Kasir
Route::middleware('auth')->group(function () {
    Route::get('/kasir', [CashierTransactionController::class, 'index'])->name('cashier.index');
    
    // API/Endpoint untuk AJAX di halaman kasir
    Route::post('/api/transactions', [CashierTransactionController::class, 'store']);
    Route::post('/api/transactions/{id}/procedures', [CashierTransactionController::class, 'addProcedure']);
    Route::delete('/api/transactions/{id}/procedures/{detailId}', [CashierTransactionController::class, 'removeProcedure']);
    Route::post('/api/transactions/{id}/pay', [CashierTransactionController::class, 'pay']);
    
    // Endpoint Cetak Struk
    Route::get('/transactions/{id}/print', [CashierTransactionController::class, 'printReceipt'])->name('transactions.print');
});

// Route Marketing
Route::get('/marketing', [MarketingDashboardController::class, 'index'])->middleware('auth')->name('marketing.dashboard');

// Ini bawaan Laravel Breeze, biarkan saja
require __DIR__.'/auth.php';