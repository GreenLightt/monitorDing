<?php
namespace Redgo\MonitorDing;

use Illuminate\Support\ServiceProvider;

class MonitorDingServiceProvider extends ServiceProvider {

    /**
     * Boot the provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config.php' => config_path('monitorDing.php'),
        ]);

    }

    /**
     * 在容器中注册绑定.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(MonitorDingClient::class, function ($app) {
            return;
        });
        $this->app->alias(MonitorDingClient::class, 'monitorDing');
    }
}