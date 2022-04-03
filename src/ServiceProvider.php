<?php

namespace CustomD\EloquentAsyncKeys;

use CustomD\EloquentAsyncKeys\Console\Asynckey;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    protected const CONFIG_PATH = __DIR__ . '/../config/eloquent-async-keys.php';

    /**
     * Undocumented function
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            self::CONFIG_PATH => config_path('eloquent-async-keys.php'),
        ], 'config');
    }

    /**
     * {@inheritDoc}
     */
    public function register()
    {
        $this->mergeConfigFrom(
            self::CONFIG_PATH,
            'eloquent-async-keys'
        );

        $this->app->singleton('eloquent-async-keys', static function ($app) {
            return new Keypair($app['config']['eloquent-async-keys']);
        });

        $this->app->singleton('command.asynckey', static function () {
            return new Asynckey();
        });

        $this->commands('command.asynckey');
    }
}
