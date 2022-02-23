<?php

namespace CustomD\EloquentAsyncKeys\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \CustomD\EloquentAsyncKeys\Keypair
 */
class EloquentAsyncKeys extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'eloquent-async-keys';
    }
}
