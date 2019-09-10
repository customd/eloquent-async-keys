<?php

namespace CustomD\EloquentAsyncKeys\Tests;

use CustomD\EloquentAsyncKeys\Facades\EloquentAsyncKeys;
use CustomD\EloquentAsyncKeys\ServiceProvider;
use Orchestra\Testbench\TestCase;

class EloquentAsyncKeysTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'eloquent-async-keys' => EloquentAsyncKeys::class,
        ];
    }

    public function testExample()
    {
        $this->assertEquals(1, 1);
    }
}
