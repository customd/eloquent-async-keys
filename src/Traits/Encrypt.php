<?php

namespace CustomD\EloquentAsyncKeys\Traits;

use CustomD\EloquentAsyncKeys\Exceptions\Exception;
use CustomD\EloquentAsyncKeys\Exceptions\MaxLengthException;
use Illuminate\Support\Facades\Log;

trait Encrypt
{
    /**
     * Encrypt data with provided public certificate.
     *
     * @param string $data Data to encrypt
     *
     * @throws Exception
     *
     * @return array{'keys': array<int, string>, 'cipherText': string} Encrypted data
     */
    protected function performEncryption(string $data, ?string $version = null): array
    {
        throw_if($this->publicKey === null, Exception::class, 'No public keys provided to encrypt with');

        $encryptionVersion = $this->getVersion($version);
        $algorithmIv = $this->generateIV($encryptionVersion);
        $algorithm = $this->versions[$encryptionVersion];

        $publicKeys = collect((array)$this->publicKey)->map(function ($publicKey, $id) {
             $key = openssl_pkey_get_public($publicKey);
            if (! $key) {
                Log::critical('Public key id: [' . $id . '] Is invalid');
            }
             return $key;
        })->filter();

        $encryptedData = null;
        $envelopeKeys = [];
        $mappedKeys = [];

        if (openssl_seal($data, $encryptedData, $envelopeKeys, $publicKeys->toArray(), $algorithm, $algorithmIv)) {
            $i = 0;
            // Ensure each shareKey is labelled with its corresponding key id
            foreach ($publicKeys as $keyId => $publicKey) {
                $mappedKeys[$keyId] = base64_encode($envelopeKeys[$i++]);
            }

            return [
                'keys'       => $mappedKeys,
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
     * @return array{'keys':array<int, string>, 'cipherText':string} Base64-encrypted data
     */
    public function encrypt($data, ?string $version = null): array
    {

        return $this->performEncryption($data, $version);
    }

    /**
     * Encrypts the data with a supplied key.
     *
     * @param string|array<int, string> $publicKey
     * @param string $data
     * @param ?string $version
     *
     * @return array{'keys':array<int, string>, 'cipherText':string}
     */
    public function encryptWithKey(string|array $publicKey, $data, ?string $version = null): array
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
