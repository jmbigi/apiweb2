<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('global', fn (Request $request) => Limit::perMinute(120)->by($request->ip()));

        RateLimiter::for('sitemap', fn (Request $request) => Limit::perMinute(30)->by($request->ip()));

        RateLimiter::for('pdf', fn (Request $request) => Limit::perMinute(30)->by($request->ip()));

        $this->blockDestructiveCommands();
    }

    private function blockDestructiveCommands(): void
    {
        $blocked = ['migrate:fresh', 'migrate:refresh', 'migrate:reset', 'db:wipe'];

        Event::listen(function (CommandStarting $event) use ($blocked) {
            if (in_array($event->command, $blocked) && $this->app->environment('production')) {
                echo "\n🔴 Comando '{$event->command}' bloqueado en producción.\n\n";
                exit(1);
            }
        });
    }
}
