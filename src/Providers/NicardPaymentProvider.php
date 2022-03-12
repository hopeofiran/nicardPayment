<?php

namespace HopeOfIran\NicardPayment\Providers;

use HopeOfIran\NicardPayment\NicardPayment;
use Illuminate\Support\ServiceProvider;

class NicardPaymentProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->publishes([
            NicardPayment::getDefaultConfigPath() => config_path('nicardPayment.php'),
        ]);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind('nicard-payment', function () {
            $config = config('nicardPayment') ?? [];
            return new NicardPayment($config);
        });
    }
}
