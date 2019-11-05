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
     * @return array Encrypted data
     */
    protected function performEncryption($data, $version = null): array
    {
        $encryptionVersion = $this->getVersion($version);

        $algorithmIv = $this->generateIV($encryptionVersion);
        $algorithm = $this->versions[$encryptionVersion];

        $publicKeys = array_map(function ($publicKey) {
            $key = openssl_pkey_get_public($publicKey);

            if (! $key) {
                throw new Exception('Unable to get public key for encryption.');
            }

            return $key;
        }, (array) $this->publicKey);

        $encryptedData = null;
        $envelopeKeys = [];
        $mappedKeys = [];

        if (openssl_seal($data, $encryptedData, $envelopeKeys, $publicKeys, $algorithm, $algorithmIv)) {
            $i = 0;
            // Ensure each shareKey is labelled with its corresponding key id
            foreach ($publicKeys as $keyId => $publicKey) {
                $mappedKeys[$keyId] = base64_encode($envelopeKeys[$i]);
                openssl_free_key($publicKey);
                $i++;
            }

            return [
                'keys' => $mappedKeys,
                'cipherText' => base64_encode($encryptedData)
                    . ':'
                    . base64_encode($encryptionVersion)
                    . ':'
                    . base64_encode($algorithmIv),
            ];
        }

        throw new Exception('Encryption failed ' . openssl_error_string());
    }

    /**
     * Encrypt data and then optionallay base64_encode it.
     *
     * @param string $data Data to encrypt
     *
     * @return array Base64-encrypted data
     */
    public function encrypt($data, $version = null): array
    {
        // $this->testIfStringIsToLong($data);

        return $this->performEncryption($data, $version);
    }

    /**
     * Encrypts the data with a supplied key.
     *
     * @param string|array $publicKey
     * @param string $data
     *
     * @return array
     */
    public function encryptWithKey($publicKey, $data, $version = null): array
    {
        $this->publicKey = [];
        foreach ((array) $publicKey as $keyId => $key) {
            $this->publicKey[$keyId] = $this->fixKeyArgument($key);
        }

        return $this->encrypt($data, $version);
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
            throw new MaxLengthException('Encryption can be a maximum of ' . $maxlen . ' bytes');
        }
    }
}
