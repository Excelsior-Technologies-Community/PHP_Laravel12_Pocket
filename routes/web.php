<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WalletController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [WalletController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    Route::get('/wallet', [WalletController::class, 'index'])
        ->name('wallet');

    Route::post('/add-money', [WalletController::class, 'addMoney'])
        ->name('wallet.add');

    Route::post('/deduct-money', [WalletController::class, 'deductMoney'])
        ->name('wallet.deduct');

    Route::post('/transfer', [WalletController::class, 'transfer'])
        ->name('wallet.transfer');

    Route::post('/change-currency', [WalletController::class, 'changeCurrency'])
        ->name('wallet.currency');

    Route::post('/set-budget', [WalletController::class, 'setBudget'])
        ->name('wallet.budget');

    Route::delete('/transaction/{transaction}', [WalletController::class, 'destroy'])
        ->name('transaction.delete');
});

require __DIR__ . '/auth.php';