<?php

namespace Sergeich5\LaravelTelegramLogs;

use Illuminate\Support\ServiceProvider;
use Sergeich5\LaravelTelegramLogs\Commands\TelegramGetUpdates;

class TelegramLogsServiceProvider extends ServiceProvider
{
    function boot()
    {
        $this->registerCommands();
    }

    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                TelegramGetUpdates::class,
            ]);
        }
    }
}
