<?php

namespace CustomD\EloquentAsyncKeys\Facades;

use Illuminate\Support\Facades\Facade;

class EloquentAsyncKeys extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'eloquent-async-keys';
    }
}
