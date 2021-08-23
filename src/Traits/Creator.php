<?php

namespace CustomD\EloquentAsyncKeys\Traits;

use CustomD\EloquentAsyncKeys\Exceptions\Exception;

trait Creator
{
    /**
     * Creates a new RSA key pair with the given key size.
     *
     * @param null $keySize   RSA Key Size in bits
     * @param bool $overwrite Overwrite existing key files
     *
     * @return self
     */
    public function create($keySize = null, $overwrite = false): self
    {
        $keySize = $this->getKeySize($keySize);

        if (! $overwrite) {
            if ($this->keyFileExists($this->publicKey) || $this->keyFileExists($this->privateKey)) {
                throw new Exception('Existing keys found. Remove keys or pass $overwrite == true / --overwrite .');
            }
        }

        $resource = openssl_pkey_new([
            'private_key_bits' => $keySize,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        $this->setupPublicKey($resource);
        $this->setupPrivateKey($resource);

        if (\PHP_VERSION_ID < 80000) {
            openssl_pkey_free($resource);
        }

        return $this;
    }

    /**
     * Generates the public key and stores it.
     *
     * @param resource $resource
     *
     * @throws Exception
     */
    protected function setupPublicKey($resource): void
    {
        $publicKey = openssl_pkey_get_details($resource)['key'];

        if (strpos($this->publicKey, 'file://') === 0) {
            $bytes = file_put_contents($this->publicKey, $publicKey);
        } else {
            $this->publicKey = $publicKey;
            $bytes = strlen($publicKey);
        }

        if (strlen($publicKey) < 1 || $bytes !== strlen($publicKey)) {
            throw new Exception('OpenSSL: Error writing PUBLIC key.');
        }
    }

    /**
     * Generates the private key and stores it.
     *
     * @param resource $resource
     *
     * @throws Exception
     */
    protected function setupPrivateKey($resource): void
    {
        $privateKey = '';

        openssl_pkey_export($resource, $privateKey, $this->saltedPassword());

        if (strpos($this->privateKey, 'file://') === 0) {
            $bytes = file_put_contents($this->privateKey, $privateKey);
        } else {
            $this->privateKey = $privateKey;
            $bytes = strlen($privateKey);
        }

        if (strlen($privateKey) < 1 || $bytes !== strlen($privateKey)) {
            throw new Exception('Error writing PRIVATE key.');
        }
    }
}
