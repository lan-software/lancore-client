<?php

namespace LanSoftware\LanCoreClient;

use Illuminate\Support\ServiceProvider;
use LanSoftware\LanCoreClient\Webhooks\Middleware\VerifyLanCoreWebhook;

class LanCoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/lancore.php', 'lancore');

        $this->app->scoped(LanCoreClient::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/lancore.php' => config_path('lancore.php'),
            ], 'lancore-config');
        }

        $this->app['router']->aliasMiddleware('lancore.webhook', VerifyLanCoreWebhook::class);
    }
}
