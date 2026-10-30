<?php

namespace App\Providers;

use App\Models\AppNotification;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('layouts.navigation', function ($view): void {
            $unread = 0;
            if (auth()->check()) {
                $unread = AppNotification::query()
                    ->where('user_id', auth()->id())
                    ->unread()
                    ->count();
            }
            $view->with('unreadNotificationsCount', $unread);
        });
    }
}
