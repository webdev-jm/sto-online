<?php

namespace App\Providers;

use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Support\FormBuilder;
use App\Services\GeminiService;
use App\Services\OllamaService;

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

        // Resolves to the chat/embedding provider selected by AI_PROVIDER (see config/services.php).
        $aiProvider = fn($app) => config('services.ai.driver') === 'gemini'
            ? $app->make(GeminiService::class)
            : $app->make(OllamaService::class);

        $this->app->bind('ai.chat', $aiProvider);
        $this->app->bind('ai.embed', $aiProvider);
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
