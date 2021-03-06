<?php

namespace CustomD\EloquentAsyncKeys;

use CustomD\EloquentAsyncKeys\Traits\Creator;
use CustomD\EloquentAsyncKeys\Traits\Decrypt;
use CustomD\EloquentAsyncKeys\Traits\Encrypt;
use CustomD\EloquentAsyncKeys\Traits\Setters;
use CustomD\EloquentAsyncKeys\Exceptions\InvalidKeysException;

class Keypair
{
    use Setters;
    use Creator;
    use Encrypt;
    use Decrypt;

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
     * Holds our key password Salt.
     *
     * @var  string|null
     */
    protected $salt = null;

    protected $versions = [];

    protected $version = null;

    /**
     * Constructor for our Keypair.
     *
     * @param array        $config
     * @param string|null $publicKey
     * @param string|null $privateKey
     * @param string|null $password
     * @param string|null $salt
     */
    public function __construct(array $config, $publicKey = null, $privateKey = null, $password = null, $salt = null)
    {
        $this->setConfig($config);
        $this->setKeys($publicKey, $privateKey, $password, $salt);
    }

    /**
     * gets the current version
     *
     * @param mixed $version
     *
     * @return mixed
     */
    public function getVersion($version = null)
    {
        if ($version === null) {
            $version = $this->version;
        }

        if (! isset($this->versions[$version])) {
            end($this->versions);
            $version = key($this->versions);
        }

        return $version;
    }

    /**
     * generates a new IV string
     *
     * @param mixed $version
     *
     * @return string
     */
    public function generateIV($version): string
    {
        $cipher = $this->versions[$version];
        $len = openssl_cipher_iv_length($cipher);

        if ((int) $len === 0) {
            return '';
        }

        return \random_bytes($len);
    }

    /**
     * Reset function to setup for new round of keys.
     *
     * @return self
     */
    public function reset(): self
    {
        $this->publicKey = null;
        $this->privateKey = null;
        $this->password = null;
        $this->salt = null;

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
            return 'file://' . $keyFile;
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
    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    /**
     * Get private key to be used during encryption and decryption.
     *
     * @return string Certificate private key string or stream path
     */
    public function getPrivateKey(): ?string
    {
        return $this->privateKey;
    }

    /**
     * Get salt to be used during encryption and decryption.
     *
     * @return string|null Salt or null if not set.
     */
    public function getSalt(): ?string
    {
        return $this->salt;
    }

    /**
     * Get private key PEM to be used during encryption and decryption.
     *
     * @param bool $decrypt - decrypt encrypted private key or not
     *
     * @return string Certificate private key string
     */
    public function getDecryptedPrivateKey()
    {
        $privKey = openssl_pkey_get_private($this->privateKey, $this->saltedPassword());
        openssl_pkey_export($privKey, $return);

        return $return;
    }

    /**
     * generates our salted password.
     *
     * @return string
     */
    protected function saltedPassword(): ?string
    {
        if ($this->password === null) {
            return null;
        }
        $salt = $this->salt === null ? sha1($this->password) : $this->salt;
        // NIST recommendation is 10k iterations.
        return hash_pbkdf2('sha256', $this->password, $salt, 10000, 50);
    }

    /**
     * Method that checks through 1 or more public keys are valid and throw an exception if any are broken.
     *
     * @throws InvalidKeysException
     */
    public function checkPublicKey($publicKey): void
    {
         $invalidKeys = collect($publicKey)->map(function($publicKey, $id) {
              $key = openssl_pkey_get_public($publicKey);
              if(!$key){
                return $id;
              }
              return null;
        })->filter();

        if($invalidKeys->isNotEmpty()){
            $exception = new InvalidKeysException('The following public keys are invalid');
            $exception->setKeys($invalidKeys->toArray());
            throw $exception;
        }

    }
}
