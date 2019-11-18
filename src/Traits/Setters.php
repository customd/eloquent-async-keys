<?php

namespace CustomD\EloquentAsyncKeys\Traits;

use CustomD\EloquentAsyncKeys\Exceptions\Exception;

trait Setters
{

    /**
     * sets the config versions
     *
     * @param array $config
     *
     * @return void
     */
    public function setConfig(array $config): self
    {
        $this->versions = $config['versions'];
        $this->version = $config['default'];
        return $this;
    }

    /**
     * Sets our current keys / passwords values.
     *
     * @param string $publicKey
     * @param string $privateKey
     * @param string $password
     * @param string|bool $salt - set to true to generate a new random one
     *
     * @return self
     */
    public function setKeys($publicKey = null, $privateKey = null, $password = null, $salt = null): self
    {
        $this->reset();

        $this->setPublicKey($publicKey);
        $this->setPrivateKey($privateKey);
        $this->setPassword($password);
        $this->setSalt($salt);

        return $this;
    }

    /**
     * set the current public key.
     *
     * @param string|null $publicKey
     *
     * @return self
     */
    public function setPublicKey(?string $publicKey = null): self
    {
        $this->publicKey = $this->fixKeyArgument($publicKey);

        return $this;
    }

    /**
     * sets the current private key.
     *
     * @param string|null $privateKey
     *
     * @return self
     */
    public function setPrivateKey(?string $privateKey = null): self
    {
        $this->privateKey = $this->fixKeyArgument($privateKey);

        return $this;
    }

    /**
     * Set password to be used during encryption and decryption.
     *
     * @param string|null $password Certificate password
     *
     * @return self
     */
    public function setPassword(?string $password = null): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Set the salt / generate a new one / clear the salt.
     *
     * @param bool|string|null $salt
     *
     * @return self
     */
    public function setSalt($salt = null): self
    {
        if ($salt === true) {
            $salt = bin2hex(random_bytes(16));
        }
        $this->salt = $salt;

        return $this;
    }

    /**
     * Method to set a new password onto the private key.
     *
     * @param string $newPassword
     * @param bool|string|null $newSalt
     */
    public function setNewPassword(string $newPassword, $newSalt = false): void
    {
        $decryptedPrivateKey = $this->getDecryptedPrivateKey();

        if (! $decryptedPrivateKey) {
            throw new Exception('could not decrypt private key');
        }

        $this->setPassword($newPassword);

        if ($newSalt !== false) {
            $this->setSalt($newSalt);
        }

        if (openssl_pkey_export($decryptedPrivateKey, $privateKey, $this->saltedPassword()) === false) {
            throw new Exception('Passphrase change failed: ' . openssl_error_string());
        }

        $this->privateKey = $privateKey;

        return $this;
    }
}
