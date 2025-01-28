<?php

namespace CustomD\EloquentAsyncKeys;

use CustomD\EloquentAsyncKeys\Exceptions\Exception;
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
     * @var string|array<int, string>|null
     */
    protected string|array|null $publicKey;

     /**
     * @var string|array<int, string>|null
     */
    protected string|array|null $privateKey;

    protected ?string $password;

    protected ?string $salt = null;

    /**
     * @var array<string, string> $versions
     */
    protected array $versions = [];

    protected string $version;

    /**
     * Constructor for our Keypair.
     *
     * @param array{versions: array<string,string>, default: string}        $config
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
     * @param ?string $version
     *
     * @return string
     */
    public function getVersion(?string $version = null): string
    {
        $version ??= $this->version;

        if (! isset($this->versions[$version])) {
            end($this->versions);
            $version = strval(key($this->versions));
        }

        return $version;
    }

    /**
     * generates a new IV string
     *
     * @param string $version
     *
     * @return string
     */
    public function generateIV(string $version): string
    {
        $cipher = $this->versions[$version];
        $len = openssl_cipher_iv_length($cipher);

        if ($len === false || $len < 1) {
            throw new Exception("Invalid cipher detected");
        }

        return \random_bytes($len);
    }

    /**
     * Reset function to setup for new round of keys.
     *
     * @return static
     */
    public function reset(): static
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
    protected function fixKeyArgument(string $keyFile): string
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
    protected function keyFileExists(?string $keyFile): bool
    {
        return ! is_null($keyFile) && strpos($keyFile, 'file://') === 0 && file_exists($keyFile);
    }

    /**
     * gets the keysize and makes sure that it is returned within the guidelines.
     *
     * @param ?int $keySize
     *
     * @return int
     */
    public function getKeySize(?int $keySize = null): int
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
     * @return string|array<int,string>|null Certificate public key string or stream path
     */
    public function getPublicKey(): string|array|null
    {
        return $this->publicKey;
    }

    /**
     * Get private key to be used during encryption and decryption.
     *
     * @return string|array<int,string>|null Certificate private key string or stream path
     */
    public function getPrivateKey(): string|array|null
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
     * @return ?string Certificate private key string
     */
    public function getDecryptedPrivateKey(): ?string
    {
        if (! is_string($this->privateKey)) {
            return null;
        }

        $privKey = openssl_pkey_get_private($this->privateKey, $this->saltedPassword());
        if (! $privKey) {
            return null;
        }
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
        $salt = $this->salt ?? sha1($this->password);
        // NIST recommendation is 10k iterations.
        return hash_pbkdf2('sha256', $this->password, $salt, 10000, 50);
    }

    /**
     * Method that checks through 1 or more public keys are valid and throw an exception if any are broken.
     *
     * @param array<int, string> $publicKey
     *
     * @throws InvalidKeysException
     */
    public function checkPublicKey(string|array $publicKey): void
    {
        $invalidKeys = collect((array)$publicKey)
            ->filter(fn($key) =>  openssl_pkey_get_public($key) === false)
            ->map(function ($publicKey, $id) {
                $key = openssl_pkey_get_public($publicKey);
                return ! $key ? strval($id) : null;
            })
            ->filter()
            ->whenNotEmpty(fn($collection) => throw InvalidKeysException::withKeys($collection)); //@phpstan-ignore argument.type
    }
}
