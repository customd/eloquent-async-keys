<?php

namespace CustomD\EloquentAsyncKeys;

use Illuminate\Encryption\Encrypter;
use CustomD\EloquentModelEncrypt\Abstracts\Engine;
use CustomD\EloquentAsyncKeys\Facades\EloquentAsyncKeys;

class EncryptionEngine extends Engine
{
    protected $cipher = 'AES-128-CBC';

    protected $keyLen = 16;

    /**
     * Decrypt a value.
     *
     * @param string $value
     *
     * @return string
     */
    public function decrypt(string $value): ?string
    {
        if ($value) {
            $value = \decrypt($value);
        }

        return $value;
    }

    /**
     * Encrypt a value.
     *
     * @param string $value
     *
     * @return string
     */
    public function encrypt(string $value): ?string
    {
        if ($value) {
            $value = \encrypt($value);
        }

        return $value;
    }

    public function encryptMessage($plainText, $publicKey, $salt = null)
    {
        //generate our random "salt" which we will pass to decrypt
        if ($salt === null) {
            $salt = openssl_random_pseudo_bytes($this->keyLen);
        }

        //Encrypt using the salt above the original text - this we have no lenth limit on.
        $encryptionEngine = new Encrypter($salt, $this->cipher);
        $encrytedText = $encryptionEngine->encrypt($plainText);

        // Now we get our symbolic key encrypted using the users public key
        $encryptedSalt = EloquentAsyncKeys::encryptWithKey($publicKey, $salt, true);

        $encryptedStringLength = strlen($encryptedSalt); // Get the length of the encrypted string

        $keyLength = dechex($encryptedStringLength); // The first 3 bytes of the message are the key length
        $keyLength = str_pad($keyLength, 3, '0', STR_PAD_LEFT); // Zero pad to be sure.

        // Concatenate the length, the encrypted symmetric key, and the message
        return $keyLength.$encryptedSalt.$encrytedText;
    }

    public function decryptMessage($encryptedMessage, $privateKey)
    {
        // Extract the Symmetric Key
        $keylen = substr($encryptedMessage, 0, 3);
        $len = hexdec($keylen);
        $salt = substr($encryptedMessage, 3, $len);

        //Extract the encrypted message
        $encryptedMessage = substr($encryptedMessage, 3);
        $encrytedText = substr($encryptedMessage, $len);

        $res = EloquentAsyncKeys::decryptWithKey($privateKey, $salt, true);

        $encryptor = new Encrypter($res);

        return $encryptor->decrypt($encrytedText);
    }
}
