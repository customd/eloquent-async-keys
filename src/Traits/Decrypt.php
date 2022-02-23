<?php

namespace CustomD\EloquentAsyncKeys\Traits;

use CustomD\EloquentAsyncKeys\Exceptions\Exception;

trait Decrypt
{
    /**
     * @param string $cipherData
     *
     * @return array<string, string>
     */
    public function parseCipherData(string $cipherData): array
    {
        $cipherParts = explode(':', $cipherData);

        return [
            'cipherText' => base64_decode($cipherParts[0]),
            'version'    => base64_decode($cipherParts[1]),
            'iv'         => base64_decode($cipherParts[2]),
        ];
    }

    /**
     * Decrypt data with provided private certificate.
     *
     * @param string $cipherData Data to encrypt
     * @param string $key
     *
     * @throws Exception
     *
     * @return string Decrypted data
     */
    protected function performDecryption(string $cipherData, string $key): string
    {
        if ($this->privateKey === null) {
            throw new Exception('Unable to decrypt: No private key provided.');
        }
        $decryptedData = null;

        $privateKey = openssl_pkey_get_private($this->privateKey, $this->saltedPassword());

        if (! $privateKey) {
            throw new Exception('Unable to get private key for decryption. Does this key require a password??');
        }

        [
            'cipherText' => $cipherText,
            'version'    => $version,
            'iv'         => $algorithmIv
        ] = $this->parseCipherData($cipherData);

        $encryptionVersion = $this->getVersion($version);
        $algorithm = $this->versions[$encryptionVersion];

        if (openssl_open($cipherText, $decryptedData, base64_decode($key), $privateKey, $algorithm, $algorithmIv)) {
            return $decryptedData;
        }

        throw new Exception('Decryption failed. Ensure you are using (1) a PRIVATE key, and (2) the correct one.');
    }

    /**
     * optional base64_decode data and then decrypt it.
     *
     * @param string $cipherText  data to decrypt
     * @param string $key
     *
     * @return string Decrypted data
     */
    public function decrypt(string $cipherText, string $key): string
    {
        return $this->performDecryption($cipherText, $key);
    }

    /**
     * Decrypts with a provided key.
     *
     * @param string $privateKey
     * @param string $cipherText
     *
     * @return string
     */
    public function decryptWithKey(string $privateKey, string $cipherText, string $key): string
    {
        $this->privateKey = $privateKey;

        return $this->decrypt($cipherText, $key);
    }
}
