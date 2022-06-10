<?php

namespace CustomD\EloquentAsyncKeys\Exceptions;

use Illuminate\Support\Collection;

/** @phpstan-consistent-constructor */
class InvalidKeysException extends \Exception
{
    /**
     * @var Collection<int, string>
     */
    protected Collection $keys;

    /**
     * Throw a new exception with the keys
     *
     * @param Collection<int, string> $keys
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     *
     * @return self
     */
    public static function withKeys(Collection $keys, string $message = 'The following public keys are invalid', int $code = 0, ?\Throwable $previous = null): self
    {
        $instance = new self($message, $code, $previous);
        $instance->keys = $keys;
        return $instance;
    }

    /**
     * Sets the keys that are in error
     *
     * @param Collection<int, string>  $keys
     *
     * @return void
     */
    public function setKeys(Collection $keys): void
    {
        $this->keys = $keys;
    }

    /**
     * Undocumented function
     *
     * @return \Illuminate\Support\Collection<int, string>
     */
    public function getKeys(): Collection
    {
        return $this->keys;
    }
}
