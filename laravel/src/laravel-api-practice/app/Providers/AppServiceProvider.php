<?php

namespace App\Providers;

use App\PushNotification\Repositories\LineBotMessageRepository;
use DateTimeImmutable;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(DateTimeImmutable::class, fn() => new DateTimeImmutable());

        $this->app->when(LineBotMessageRepository::class)
            ->needs('$channel_access_token')
            ->giveConfig('services.line_bot.access_token');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
