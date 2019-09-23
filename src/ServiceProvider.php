<?php

namespace CustomD\EloquentAsyncKeys;

use CustomD\EloquentAsyncKeys\Console\Asynckey;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    protected const CONFIG_PATH = __DIR__ . '/../config/eloquent-async-keys.php';

    protected const MIGRATIONS_PATH = __DIR__ . '/../database/migrations/';

    public function boot()
    {
        $this->publishes([
            self::CONFIG_PATH => config_path('eloquent-async-keys.php'),
        ], 'config');

        //set our migratinos directory
        $this->loadMigrationsFrom(self::MIGRATIONS_PATH);
    }

    public function register()
    {
        $this->mergeConfigFrom(
            self::CONFIG_PATH,
            'eloquent-async-keys'
        );

        $this->app->bind('eloquent-async-keys', static function () {
            return new Keypair();
        });

        $this->app->singleton('command.asynckey', function () {
            return new Asynckey();
        });

        $this->commands('command.asynckey');
    }
}
