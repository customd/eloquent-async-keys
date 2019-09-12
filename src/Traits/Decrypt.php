<?php

namespace CustomD\EloquentAsyncKeys\Traits;

use CustomD\EloquentAsyncKeys\Exceptions\Exception;

trait Decrypt
{
    /**
     * Decrypt data with provided private certificate.
     *
     * @param string $data Data to encrypt
     *
     * @throws Exception
     *
     * @return string Decrypted data
     */
    protected function _decrypt($data): string
    {
        if ($this->privateKey === null) {
            throw new Exception('Unable to decrypt: No private key provided.');
        }
        $privateKey = openssl_pkey_get_private($this->privateKey, $this->password);

        if (! $privateKey) {
            throw new Exception('Unable to get private key for decryption. Does this key require a password??');
        }
        $success = openssl_private_decrypt($data, $decryptedData, $privateKey);
        openssl_free_key($privateKey);

        if (! $success) {
            throw new Exception('Decryption failed. Ensure you are using (1) a PRIVATE key, and (2) the correct one.');
        }

        return $decryptedData;
    }

    /**
     * optional base64_decode data and then decrypt it.
     *
     * @param string $data  data to decrypt
     * @param string $decode - base64 decode the data before decrypting?
     *
     * @return string Decrypted data
     */
    public function decrypt($data, $decode = false): string
    {
        return $this->_decrypt($decode ? base64_decode($data) : $data);
    }

    public function decryptWithKey($privateKey, $data, $decode = false): string
    {
        $this->privateKey = $privateKey;

        return $this->decrypt($data, $decode);
    }
}
