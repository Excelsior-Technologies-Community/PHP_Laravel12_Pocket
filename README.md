# PHP_Laravel12_Pocket

##  Introduction

PHP_Laravel12_Pocket is a modern Laravel 12-based digital wallet system designed to simulate real-world financial applications such as Paytm and PhonePe.

This project demonstrates a secure and scalable wallet architecture where users can manage their balance, perform transactions, and transfer money between accounts.

The application focuses on implementing real-world backend concepts such as database transactions, relationship handling, and secure balance updates to ensure data consistency and reliability.

It is an ideal project for learning and showcasing practical backend development skills using Laravel.

---

##  Project Overview

This project is a fully functional wallet (pocket) system built using Laravel 12, following MVC architecture and best development practices.

###  Key Functionalities

- User Authentication using Laravel Breeze  
- Automatic Wallet Creation for each user  
- Add Money (Credit)  
- Deduct Money (Debit)  
- Transfer Money between users  
- Transaction History tracking  
- Secure database transactions to prevent data inconsistency  
- Clean and responsive UI using Tailwind CSS  

###  Technical Highlights

- Laravel 12 Framework  
- MVC Architecture  
- Eloquent ORM Relationships  
- Database Transactions (DB::transaction, commit, rollback)  
- Wallet locking using `lockForUpdate()` for concurrency safety  
- Clean and maintainable code structure  
- Blade templating for frontend  
- Vite + Tailwind for modern UI  

###  Real-World Concepts Covered

- Digital wallet system design  
- Financial transaction handling  
- Data integrity and concurrency control  
- User-to-user money transfer logic  
- Secure balance updates  

###  Purpose

This project is designed to demonstrate practical backend development skills and simulate real-world fintech system behavior, making it suitable for learning, internships, and portfolio showcasing.

## Features

- User Authentication (Laravel Breeze)
- Wallet Creation for each user
- Add Balance (Credit)
- Deduct Balance (Debit)
- Transfer Money Between Users
- Transaction History
- Database Transactions for safety
- Clean MVC Structure

---

##  Requirements

Before starting, make sure you have:

- PHP >= 8.2
- Composer
- Node.js & npm
- MySQL / MariaDB
- Laravel 12

---

##  Step 1: Create Laravel Project

```bash
composer create-project laravel/laravel PHP_Laravel12_Pocket "12.*"
cd PHP_Laravel12_Pocket
```
---

## Step 2: Setup Environment

Update .env file:

```.env
DB_DATABASE=laravel12_pocket
DB_USERNAME=root
DB_PASSWORD=
```

Run Migration Command:

```bash
php artisan migrate
```
---

## Step 3: Install Authentication (Breeze)

```bash
composer require laravel/breeze --dev
php artisan breeze:install
npm install
npm run dev
```
---

## Step 4: Create Wallet Model & Migration

Create command:

```bash
php artisan make:model Wallet -m
```
This Creates:

```
app/Models/Wallet.php
database/migrations/xxxx_xx_xx_xxxxxx_create_wallets_table.php
```
### Migration:

File: database/migrations/...create_wallets_table.php

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('balance', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
```

### Model

File: app/Models/Wallet.php

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'wallets';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'balance',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'balance' => 'decimal:2',
    ];

    /**
     * Get the user that owns the wallet.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

---

## Step 5: Create Transaction Model & Migration

Create command:

```bash
php artisan make:model Transaction -m
```
This Creates:

```
app/Models/Transaction.php
database/migrations/xxxx_xx_xx_xxxxxx_create_transactions_table.php
```

### Migration:

File: database/migrations/...create_transactions_table.php

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['credit', 'debit', 'transfer']);
            $table->decimal('amount', 10, 2);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
```

### Model

File: app/Models/Transaction.php

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'transactions';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'description',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Get the user that owns the transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```
---

## Step 6: Run Migration

```bash
php artisan migrate
```

---

## Step 7: Setup Relationships in User Model

File: app/Models/User.php

```php
<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

//  Import models
use App\Models\Wallet;
use App\Models\Transaction;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * =========================
     * RELATIONSHIPS
     * =========================
     */

    /**
     * One User has One Wallet
     */
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * One User has Many Transactions
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
```

---

## Step 8: Auto Create Wallet on Register

File: App\Providers\EventServiceProvider.php

```php
<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

// Import Models
use App\Models\User;
use App\Models\Wallet;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register any events for your application.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any events for your application.
     */
    public function boot(): void
    {
        //  Create wallet automatically when a user is created
        User::created(function ($user) {
            Wallet::create([
                'user_id' => $user->id,
                'balance' => 0
            ]);
        });
    }
}
```
---

## Step 9: Create Controller

```bash
php artisan make:controller WalletController
```
File: app/Http/Controllers/WalletController.php

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Transaction;

class WalletController extends Controller
{
    /**
     * Show wallet dashboard
     */
    public function index()
    {
        $user = Auth::user();

        //  Ensure wallet exists
        $wallet = $user->wallet()->firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );

        $transactions = $user->transactions()->latest()->get();

        return view('dashboard', compact('wallet', 'transactions'));
    }

    /**
     * Add money to wallet
     */
    public function addMoney(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);

        DB::transaction(function () use ($request) {

            $user = Auth::user();

            //  Ensure wallet exists
            $wallet = $user->wallet()->firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0]
            );

            $wallet->increment('balance', $request->amount);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'credit',
                'amount' => $request->amount,
                'description' => 'Money added'
            ]);
        });

        return back()->with('success', 'Money added successfully!');
    }

    /**
     * Deduct money from wallet
     */
    public function deductMoney(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);

        $user = Auth::user();

        //  Ensure wallet exists
        $wallet = $user->wallet()->firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );

        //  Check insufficient balance
        if ($wallet->balance < $request->amount) {
            return back()->with('error', 'Insufficient balance!');
        }

        DB::transaction(function () use ($request, $wallet, $user) {

            $wallet->decrement('balance', $request->amount);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'debit',
                'amount' => $request->amount,
                'description' => 'Money deducted'
            ]);
        });

        return back()->with('success', 'Money deducted successfully!');
    }

    /**
     * Transfer money between users
     */
  public function transfer(Request $request)
{
    $request->validate([
        'email' => 'required|exists:users,email',
        'amount' => 'required|numeric|min:1'
    ]);

    $sender = Auth::user();
    $receiver = User::where('email', $request->email)->first();

    //  Prevent self transfer
    if ($sender->id === $receiver->id) {
        return back()->with('error', 'You cannot transfer money to yourself.');
    }

    DB::beginTransaction();

    try {
        $senderWallet = $sender->wallet()->lockForUpdate()->first();

        if (!$senderWallet) {
            return back()->with('error', 'Sender wallet not found!');
        }

        // Create receiver wallet if not exists
        $receiverWallet = $receiver->wallet()->lockForUpdate()->first();

        if (!$receiverWallet) {
            $receiverWallet = $receiver->wallet()->create([
                'balance' => 0
            ]);
        }

        if ($senderWallet->balance < $request->amount) {
            return back()->with('error', 'Insufficient balance!');
        }

        // Deduct from sender
        $senderWallet->decrement('balance', $request->amount);

        // Add to receiver
        $receiverWallet->increment('balance', $request->amount);

        // Transactions
        Transaction::create([
            'user_id' => $sender->id,
            'type' => 'debit',
            'amount' => $request->amount,
            'description' => 'Sent to ' . $receiver->email
        ]);

        Transaction::create([
            'user_id' => $receiver->id,
            'type' => 'credit',
            'amount' => $request->amount,
            'description' => 'Received from ' . $sender->email
        ]);

        DB::commit();

        return back()->with('success', 'Money transferred successfully!');

    } catch (\Exception $e) {
        DB::rollBack();

        return back()->with('error', 'Transfer failed!');
    }
}
}
```
---

## Step 10: Routes

File: routes/web.php
	
```php
<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
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
Route::middleware('auth')->group(function () {

    /*
    |--------------------------
    | Profile Routes
    |--------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |--------------------------
    | Wallet Routes
    |--------------------------
    */
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet');

    Route::post('/add-money', [WalletController::class, 'addMoney'])->name('wallet.add');
    Route::post('/deduct-money', [WalletController::class, 'deductMoney'])->name('wallet.deduct');
    Route::post('/transfer', [WalletController::class, 'transfer'])->name('wallet.transfer');
});

/*
|--------------------------------------------------------------------------
| Auth Routes (Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
```

---

## Step 11: Update Dashboard View

File: resources/views/dashboard.blade.php

```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800">
            💰 Wallet Dashboard
        </h2>
    </x-slot>

    <div class="py-10 bg-gray-100 min-h-screen">

        <div class="max-w-4xl mx-auto px-4">

            <!-- Wallet Balance -->
            <div class="bg-gradient-to-r from-indigo-600 to-blue-500 text-white p-6 rounded-2xl shadow-lg mb-6">
                <p class="text-sm opacity-80">Wallet Balance</p>
                <h1 class="text-4xl font-bold">₹{{ $wallet?->balance ?? 0 }}</h1>
            </div>

            <!-- Messages -->
            @if(session('success'))
                <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Forms Grid -->
            <div class="grid md:grid-cols-3 gap-6">

                <!-- Add Money -->
                <div class="bg-white p-5 rounded-xl shadow">
                    <h3 class="font-semibold text-blue-600 mb-3">➕ Add Money</h3>

                    <form method="POST" action="/add-money">
                        @csrf

                        <input type="number" name="amount" placeholder="Enter amount"
                            class="w-full border p-2 rounded mb-3 focus:ring-2 focus:ring-blue-400">

                        <!-- IMPORTANT: FULL WIDTH BUTTON -->
                        <button class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-semibold">
                            Add Money
                        </button>
                    </form>
                </div>

                <!-- Deduct -->
                <div class="bg-white p-5 rounded-xl shadow">
                    <h3 class="font-semibold text-red-600 mb-3">➖ Deduct</h3>

                    <form method="POST" action="/deduct-money">
                        @csrf

                        <input type="number" name="amount" placeholder="Enter amount"
                            class="w-full border p-2 rounded mb-3 focus:ring-2 focus:ring-red-400">

                        <button class="w-full bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg font-semibold">
                            Deduct
                        </button>
                    </form>
                </div>

                <!-- Transfer -->
                <div class="bg-white p-5 rounded-xl shadow">
                    <h3 class="font-semibold text-green-600 mb-3">🔁 Transfer</h3>

                    <form method="POST" action="/transfer">
                        @csrf

                        <input type="email" name="email" placeholder="Receiver Email"
                            class="w-full border p-2 rounded mb-3 focus:ring-2 focus:ring-green-400">

                        <input type="number" name="amount" placeholder="Amount"
                            class="w-full border p-2 rounded mb-3 focus:ring-2 focus:ring-green-400">

                        <button class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg font-semibold">
                            Send Money
                        </button>
                    </form>
                </div>

            </div>

            <!-- Transactions -->
            <div class="bg-white p-6 rounded-xl shadow mt-8">
                <h3 class="font-semibold text-lg mb-4">📜 Transactions</h3>

                @forelse($transactions as $txn)
                    <div class="flex justify-between border-b py-3">

                        <div>
                            <p class="font-medium capitalize">{{ $txn->type }}</p>
                            <p class="text-sm text-gray-500">{{ $txn->description }}</p>
                        </div>

                        <div class="font-bold
                            {{ $txn->type === 'credit' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $txn->type === 'credit' ? '+' : '-' }}₹{{ $txn->amount }}
                        </div>

                    </div>
                @empty
                    <p class="text-gray-500">No transactions yet.</p>
                @endforelse

            </div>

        </div>
    </div>
</x-app-layout>
```

---

## Step 12: Run Project

### Terminal 1 (Laravel Backend)

```bash
php artisan serve
```
Register a User First

Open the application in your browser:

```bash
http://127.0.0.1:8000/register
```

Then Visit:

```bash
http://127.0.0.1:8000/wallet
```

### Terminal 2 (Frontend / Tailwind / Vite)

```bash
npm run dev
```
---

## Output

<img src="screenshots/Screenshot 2026-03-20 161300.png" width="1000">
 
<img src="screenshots/Screenshot 2026-03-20 170510.png" width="1000">
 
<img src="screenshots/Screenshot 2026-03-20 170831.png" width="1000">
 
<img src="screenshots/Screenshot 2026-03-20 170901.png" width="1000">

<img src="screenshots/Screenshot 2026-03-20 170925.png" width="1000">

<img src="screenshots/Screenshot 2026-03-20 171010.png" width="1000">

<img src="screenshots/Screenshot 2026-03-20 171041.png" width="1000"> 

---

## Project Structure

```
PHP_Laravel12_Pocket/
│
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── Controller.php
│   │       └── WalletController.php    (main logic)
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── Wallet.php                
│   │   └── Transaction.php           
│   │
│   └── Providers/
│
├── database/
│   ├── migrations/
│   │   ├── create_users_table.php
│   │   ├── create_wallets_table.php   
│   │   └── create_transactions_table.php 
│   │
│   └── seeders/
│
├── resources/
│   ├── views/
│   │   ├── auth/
│   │   │   └── login.blade.php
│   │   │
│   │   ├── layouts/
│   │   │   └── app.blade.php         (Breeze layout)
│   │   │
│   │   ├── dashboard.blade.php        (your wallet UI)
│   │   └── welcome.blade.php
│   │
│   ├── css/
│   │   └── app.css
│   │
│   └── js/
│       └── app.js
│
├── routes/
│   ├── web.php                        (your routes)
│   └── auth.php
│
├── public/
│   └── build/ (after npm build)
│
├── storage/
│
├── .env
├── package.json
├── vite.config.js
├── artisan
└── composer.json
```

Your PHP_Laravel12_Pocket Project is now ready!

