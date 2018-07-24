<?php
namespace Redgo\MonitorDing;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Redgo\MonitorDing\Commands\DealMessagesCommand;

class MonitorDingServiceProvider extends ServiceProvider {

    protected $commands = [
        DealMessagesCommand::class,
    ];

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

        $this->registerMiddleware('Redgo\MonitorDing\Middleware\Monitor');

        $this->loadViewsFrom(__DIR__ . '/Views', 'monitorDing');

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('monitorDing:dealMessages')->everyMinute()->withoutOverlapping();
        });
    }

    /**
     * 注册中间件
     *
     * @param  string $middleware
     */
    protected function registerMiddleware($middleware)
    {
        $kernel = $this->app['Illuminate\Contracts\Http\Kernel'];
        $kernel->pushMiddleware($middleware);
    }

    /**
     * 在容器中注册绑定.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(MonitorDingClient::class, function ($app) {
            $config = config('monitorDing');
            return new MonitorDingClient($config['webhook'], $config['curl_verify']);
        });
        $this->app->alias(MonitorDingClient::class, 'monitorDing');

        $this->commands($this->commands);
    }
}