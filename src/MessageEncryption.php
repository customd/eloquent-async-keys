<?php

namespace CustomD\EloquentAsyncKeys;

use Illuminate\Encryption\Encrypter;
use CustomD\EloquentAsyncKeys\Facades\EloquentAsyncKeys;

class MessageEncryption
{
    protected $cipher = 'AES-128-CBC';

    protected $keyLen = 16;

    public function encryptMessage($plainText, $publicKey, $key = null)
    {
        //generate our random "salt" which we will pass to decrypt
        if ($key === null) {
            $key = \random_bytes($this->keyLen);
        }

        //Encrypt using the key above the original text - this we have no lenth limit on.
        $encryptionEngine = new Encrypter($key, $this->cipher);
        $encrytedText = $encryptionEngine->encrypt($plainText);

        // Now we get our symbolic key encrypted using the users public key
        $encryptedKey = EloquentAsyncKeys::encryptWithKey($publicKey, $key, true);

        $encryptedStringLength = strlen($encryptedKey); // Get the length of the encrypted string

        $keyLength = dechex($encryptedStringLength); // The first 3 bytes of the message are the key length
        $keyLength = str_pad($keyLength, 3, '0', STR_PAD_LEFT); // Zero pad to be sure.

        // Concatenate the length, the encrypted symmetric key, and the message
        return $keyLength.$encryptedKey.$encrytedText;
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
