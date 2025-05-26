<?php

namespace PhpAiSdk\Laravel;

use Illuminate\Support\ServiceProvider;

class AiSdkServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ai.php', 'ai');

        $this->app->singleton(AiManager::class, function ($app) {
            return new AiManager($app); // AiManager constructor expects $app
        });

        $this->app->bind('ai', function ($app) {
            return $app->make(AiManager::class);
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [
                    __DIR__ . '/../config/ai.php' => config_path('ai.php'),
                ],
                'ai-config'
            );
        }
    }
}
