<?php
namespace LouisLun\LaravelUnipay;

use Illuminate\Support\ServiceProvider;

class UnipayServiceProvider extends ServiceProvider
{
    /**
     * Register services
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Unipay::class, function ($app) {
            return new Unipay($app['config']['unipay']);
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/config.php' => config_path('unipay.php'),
        ], 'unipay');
    }
}
