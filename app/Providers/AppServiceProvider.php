<?php

namespace App\Providers;

use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Support\FormBuilder;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('form.builder', fn() => new FormBuilder());
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Paginator::useBootstrap();

        Event::listen(ConnectionEstablished::class, function (ConnectionEstablished $event): void {
            if ($event->connectionName === 'sqlite_reports') {
                $event->connection->statement('PRAGMA journal_mode = WAL');
                $event->connection->statement('PRAGMA busy_timeout = 60000');
            }
        });
    }
}
