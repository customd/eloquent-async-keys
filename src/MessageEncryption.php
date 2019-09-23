<?php

namespace CustomD\EloquentAsyncKeys;

use Illuminate\Encryption\Encrypter;
use CustomD\EloquentAsyncKeys\Facades\EloquentAsyncKeys;

class MessageEncryption
{
    protected $cipher = 'AES-128-CBC';

    protected $keyLength = 16;

    protected $publicKey;

    protected $privateKey;

    protected $synchronousKey = null;

    public function __construct($publicKey = null, $privateKey = null, $synchronousKey = null)
    {
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
        $this->assignSynchronousKey($synchronousKey);
    }

    public function assignSynchronousKey($synchronousKey = null): void
    {
        if ($synchronousKey === null) {
            $synchronousKey = \random_bytes($this->keyLength);
        }

        $this->synchronousKey = $synchronousKey;
    }

    public function encrypt($plainText): string
    {
        //Encrypt using the key above the original text - this we have no lenth limit on.
        $encryptionEngine = new Encrypter($this->synchronousKey, $this->cipher);
        $ciphertext = $encryptionEngine->encrypt($plainText);

        // Now we get our symbolic key encrypted using the users public key
        $encryptedKey = EloquentAsyncKeys::encryptWithKey($this->publicKey, $this->synchronousKey, true);

        $encryptedStringLength = strlen($encryptedKey); // Get the length of the encrypted string

        $keyLength = dechex($encryptedStringLength); // The first 3 bytes of the message are the key length
        $keyLength = str_pad($keyLength, 3, '0', STR_PAD_LEFT); // Zero pad to be sure.

        // Concatenate the length, the encrypted symmetric key, and the message
        return $keyLength . $encryptedKey . $ciphertext;
    }

    public function decrypt($encryptedMessage): string
    {
        // Extract the Symmetric Key
        $keylen = substr($encryptedMessage, 0, 3);
        $len = hexdec($keylen);
        $synchronousKey = substr($encryptedMessage, 3, $len);

        //Extract the encrypted message
        $encryptedMessage = substr($encryptedMessage, 3);
        $ciphertext = substr($encryptedMessage, $len);

        $res = EloquentAsyncKeys::decryptWithKey($this->privateKey, $synchronousKey, true);

        $encryptor = new Encrypter($res, $this->cipher);

        return $encryptor->decrypt($ciphertext);
    }
}
