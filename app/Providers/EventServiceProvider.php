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