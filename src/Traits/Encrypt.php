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
     * @return string Encrypted data
     */
    protected function _encrypt($data): string
    {
        // Load public key
        $publicKey = openssl_pkey_get_public($this->publicKey);

        if (! $publicKey) {
            throw new Exception('OpenSSL: Unable to get public key for encryption. Is the location correct? Does this key require a password?');
		}

        $success = openssl_public_encrypt($data, $encryptedData, $publicKey);
        openssl_free_key($publicKey);

        if (! $success) {
            throw new Exception('Encryption failed. Ensure you are using a PUBLIC key.');
        }

        return $encryptedData;
    }

    /**
     * Encrypt data and then optionallay base64_encode it.
     *
     * @param string $data Data to encrypt
     * @param bool $encode Base64 Encode the encrypted result
     *
     * @return string Base64-encrypted data
     */
    public function encrypt($data, $encode = false): string
    {
		$this->testIfStringIsToLong($data);
        return $encode ? base64_encode($this->_encrypt($data)) : $this->_encrypt($data);
	}

	public function encryptWithKey($publicKey, $data, $encode = false): string
	{
		$this->publicKey = $publicKey;
		return $this->encrypt($data, $encode);
	}

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
            throw new Exception('OpenSSL: Unable to get private key for decryption. Is the location correct? If this key requires a password, have you supplied the correct one?');
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

	/**
	 * Undocumented function
	 *
	 * @param string $string
	 *
	 * @return void
	 */
	public function testIfStringIsToLong(string $string): void
	{

		$keylength = $this->getKeySize();
		$maxlen = ($keylength/8)-11;

		if(strlen($string) >= $maxlen)
		{
			throw new MaxLengthException("Encryption can be a maximum of " . $maxlen . " bytes");
		}
	}
}
