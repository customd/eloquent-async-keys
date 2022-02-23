<?php

namespace CustomD\EloquentAsyncKeys\Traits;

use OpenSSLAsymmetricKey;
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
    public function create(?int $keySize = null, bool $overwrite = false): self
    {
        $keySize = $this->getKeySize($keySize);

        if (! $overwrite) {
            if ($this->keyFileExists(strval($this->publicKey)) || $this->keyFileExists(strval($this->privateKey))) {
                throw new Exception('Existing keys found. Remove keys or pass $overwrite == true / --overwrite .');
            }
        }

        $resource = openssl_pkey_new([
            'private_key_bits' => $keySize,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        if ($resource === false) {
            throw new Exception('Failed to create the keys');
        }

        $this->setupPublicKey($resource);
        $this->setupPrivateKey($resource);

        return $this;
    }

    /**
     * Generates the public key and stores it.
     *
     * @param OpenSSLAsymmetricKey $resource
     *
     * @throws Exception
     */
    protected function setupPublicKey(OpenSSLAsymmetricKey $resource): void
    {
        throw_if(is_array($this->publicKey), Exception::class, "OpenSSL: Can only set a single public key with the setupPublicKey method.");
        $pkey = openssl_pkey_get_details($resource);
        throw_if($pkey === false, Exception::class, "OpenSSL: Error getting PUBLIC key details.");

        $publicKey = $pkey['key'];

        if (is_string($this->publicKey) && strpos($this->publicKey, 'file://') === 0) {
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
     * @param OpenSSLAsymmetricKey $resource
     *
     * @throws Exception
     */
    protected function setupPrivateKey(OpenSSLAsymmetricKey $resource): void
    {
        throw_if(is_array($this->privateKey), Exception::class, "OpenSSL: Can only set a single private key with the setupPrivateKey method.");

        $privateKey = '';

        openssl_pkey_export($resource, $privateKey, $this->saltedPassword());

        if (is_string($this->privateKey) && strpos($this->privateKey, 'file://') === 0) {
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
