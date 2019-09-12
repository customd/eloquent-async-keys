<?php

namespace CustomD\EloquentAsyncKeys;

use CustomD\EloquentAsyncKeys\Traits\Creator;
use CustomD\EloquentAsyncKeys\Traits\Decrypt;
use CustomD\EloquentAsyncKeys\Traits\Encrypt;

class Keys
{
    use Decrypt, Encrypt, Creator;

    /**
     * Minimum key size bits.
     */
    protected const MINIMUM_KEY_SIZE = 128;

    /**
     * Default key size bits.
     */
    protected const DEFAULT_KEY_SIZE = 2048;

    /**
     * Holds our public key.
     *
     * @var string
     */
    protected $publicKey;

    /**
     * Holds our private key.
     *
     * @var string
     */
    protected $privateKey;

    /**
     * Holds our key password if needed.
     *
     * @var string
     */
    protected $password;

    /**
     * Sets our current keys / passwords values.
     *
     * @param string $publicKey
     * @param string $privateKey
     * @param string $password
     *
     * @return self
     */
    public function setKeys($publicKey = null, $privateKey = null, $password = null): self
    {
        $this->publicKey = $this->fixKeyArgument($publicKey);
        $this->privateKey = $this->fixKeyArgument($privateKey);
        $this->password = $password;

        return $this;
    }

    /**
     * makes sure to store as a file or string depending on the way the key is passed.
     *
     * @param string $keyFile
     *
     * @return string
     */
    protected function fixKeyArgument($keyFile): ?string
    {
        $keyFile = ltrim($keyFile);

        if (strpos($keyFile, '/') === 0) {
            // This looks like a path, let us prepend the file scheme
            return 'file://'.$keyFile;
        }

        return $keyFile;
    }

    /**
     * Determines if the file already exists or not.
     *
     * @param string $keyFile
     *
     * @return bool
     */
    protected function keyFileExists($keyFile): bool
    {
        return strpos($keyFile, 'file://') === 0 && file_exists($keyFile);
    }

    /**
     * gets the keysize and makes sure that it is returned within the guidelines.
     *
     * @param int $keySize
     *
     * @return int
     */
    public function getKeySize($keySize = null): int
    {
        $keySize = intval($keySize);

        if ($keySize < self::MINIMUM_KEY_SIZE) {
            $keySize = self::DEFAULT_KEY_SIZE;
        }

        return $keySize;
    }

    /**
     * Get public key to be used during encryption and decryption.
     *
     * @return string Certificate public key string or stream path
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * Get private key to be used during encryption and decryption.
     *
     * @return string Certificate private key string or stream path
     */
    public function getPrivateKey(): string
    {
        return $this->privateKey;
    }

    /**
     * Get private key PEM to be used during encryption and decryption.
     *
     * @param bool $decrypt - decrypt encrypted private key or not
     *
     * @return resource Certificate private key string or stream path
     */
    public function getDecryptedPrivateKey()
    {
        return openssl_pkey_get_private($this->privateKey, $this->password);
    }

    /**
     * Set password to be used during encryption and decryption.
     *
     * @param string $password Certificate password
     *
     * @return self
     */
    public function setPassword($password): self
    {
        $this->password = $password;

        return $this;
    }
}
