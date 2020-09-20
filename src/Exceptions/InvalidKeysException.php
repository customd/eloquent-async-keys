<?php

namespace CustomD\EloquentAsyncKeys\Exceptions;

use Illuminate\Support\Collection;

class InvalidKeysException extends \Exception
{
    protected $keys = [];

    public function setKeys(array $keys): void
    {
        $this->keys = $keys;
    }

    public function getKeys(): Collection
    {
        return collect($this->keys);
    }
}
