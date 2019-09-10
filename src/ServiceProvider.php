<?php

namespace CustomD\EloquentAsyncKeys;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    const CONFIG_PATH = __DIR__ . '/../config/eloquent-async-keys.php';

    public function boot()
    {
        $this->publishes([
            self::CONFIG_PATH => config_path('eloquent-async-keys.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            self::CONFIG_PATH,
            'eloquent-async-keys'
        );

        $this->app->bind('eloquent-async-keys', function () {
            return new EloquentAsyncKeys();
        });
    }
}
