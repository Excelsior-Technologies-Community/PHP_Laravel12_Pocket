<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WalletController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Welcome Page
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', [WalletController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Profile Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | Wallet Dashboard
    |--------------------------------------------------------------------------
    */
    Route::get('/wallet', [WalletController::class, 'index'])
        ->name('wallet');

    /*
    |--------------------------------------------------------------------------
    | Wallet Actions
    |--------------------------------------------------------------------------
    */

    // Add Money
    Route::post('/add-money', [WalletController::class, 'addMoney'])
        ->name('wallet.add');

    // Deduct Money
    Route::post('/deduct-money', [WalletController::class, 'deductMoney'])
        ->name('wallet.deduct');

    // Transfer Money
    Route::post('/transfer', [WalletController::class, 'transfer'])
        ->name('wallet.transfer');

    /*
    |--------------------------------------------------------------------------
    | Transaction Delete
    |--------------------------------------------------------------------------
    */
    Route::delete('/transaction/{transaction}', [WalletController::class, 'destroy'])
        ->name('transaction.delete');
});

/*
|--------------------------------------------------------------------------
| Breeze Authentication Routes
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';