<?php

namespace CustomD\EloquentAsyncKeys\Exceptions;

class Exception extends \Exception
{
    /**
     * Exception Hanlder for our Async Keys.
     *
     * @param string $message Message for the error
     * @param int $code code of the error
     *
     * @param \Exception $previous prevoius exception
     */
    public function __construct($message, $code = 0, ?\Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $openSSlErrorMessage = openssl_error_string();

        if ($openSSlErrorMessage) {
            // openSSL has something to say! Let us add it to the message.
            $this->message = sprintf(
                '%s%sUnderlying openSSL message : %s',
                parent::getMessage(),
                PHP_EOL,
                $openSSlErrorMessage
            );
        }
    }
}
