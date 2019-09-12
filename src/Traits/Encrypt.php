<?php

namespace CustomD\EloquentAsyncKeys\Traits;

use CustomD\EloquentAsyncKeys\Exceptions\Exception;
use CustomD\EloquentAsyncKeys\Exceptions\MaxLengthException;

trait Encrypt
{
    /**
     * Encrypt data with provided public certificate.
     *
     * @param string $data Data to encrypt
     *
     * @throws Exception
     *
     * @return string Encrypted data
     */
    protected function _encrypt($data): string
    {
        // Load public key
        $publicKey = openssl_pkey_get_public($this->publicKey);

        if (! $publicKey) {
            throw new Exception('Unable to get public key for encryption.');
        }

        $success = openssl_public_encrypt($data, $encryptedData, $publicKey);
        openssl_free_key($publicKey);

        if (! $success) {
            throw new Exception('Encryption failed. Ensure you are using a PUBLIC key.');
        }

        return $encryptedData;
    }

    /**
     * Encrypt data and then optionallay base64_encode it.
     *
     * @param string $data Data to encrypt
     * @param bool $encode Base64 Encode the encrypted result
     *
     * @return string Base64-encrypted data
     */
    public function encrypt($data, $encode = false): string
    {
        $this->testIfStringIsToLong($data);

        return $encode ? base64_encode($this->_encrypt($data)) : $this->_encrypt($data);
    }

    public function encryptWithKey($publicKey, $data, $encode = false): string
    {
        $this->publicKey = $publicKey;

        return $this->encrypt($data, $encode);
    }

    /**
     * Tests that the string we are trying to encrypt is not to long.
     *
     * @param string $string
     */
    public function testIfStringIsToLong(string $string): void
    {
        $keylength = $this->getKeySize();
        $maxlen = ($keylength / 8) - 11;

        if (strlen($string) >= $maxlen) {
            throw new MaxLengthException('Encryption can be a maximum of '.$maxlen.' bytes');
        }
    }
}
