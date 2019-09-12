<?php

namespace CustomD\EloquentAsyncKeys\Tests;

use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase;
use CustomD\EloquentAsyncKeys\Keys;
use CustomD\EloquentAsyncKeys\ServiceProvider;
use CustomD\EloquentAsyncKeys\EncryptionEngine;
use CustomD\EloquentAsyncKeys\Exceptions\Exception;
use CustomD\EloquentAsyncKeys\Facades\EloquentAsyncKeys;
use CustomD\EloquentAsyncKeys\Exceptions\MaxLengthException;

class EloquentAsyncKeysTest extends TestCase
{
    protected $publicKey = '
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAv0cHdt0Sjz5u2wzo1Kxd
2VJCmOZUSsfAlr4fBdHprHB2T8GOnRzDgrbl8ou2roIf8dhVpvec4RtxWuHneoAN
R4A029EVyM90yEjGXFHSJxLfi1wXdQEeL+cEC1kPLGNwbnc0oAsPjSFNqKWRXpdH
OK01RMgqm77Q9z2DktJjOgVMFXmaY8j6U1/rFFNeQEIZwQzHSQuVUeuSVBscm29a
vsLCKW9v7oJ4Hh+b+ddXWSLguW0Uo0u1kDwkKgRybj0hrNKVcTTwwysVa0ttM1E3
a/fg48hcUlZX2gZYvsN19vpCUMNp4FDHNiOB2I9IPK/eTXoAFe3zrYFeoVxdJuab
VQIDAQAB
-----END PUBLIC KEY-----
	';

    protected $privateKey = '
	-----BEGIN PRIVATE KEY-----
MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQC/Rwd23RKPPm7b
DOjUrF3ZUkKY5lRKx8CWvh8F0emscHZPwY6dHMOCtuXyi7augh/x2FWm95zhG3Fa
4ed6gA1HgDTb0RXIz3TISMZcUdInEt+LXBd1AR4v5wQLWQ8sY3BudzSgCw+NIU2o
pZFel0c4rTVEyCqbvtD3PYOS0mM6BUwVeZpjyPpTX+sUU15AQhnBDMdJC5VR65JU
Gxybb1q+wsIpb2/ugngeH5v511dZIuC5bRSjS7WQPCQqBHJuPSGs0pVxNPDDKxVr
S20zUTdr9+DjyFxSVlfaBli+w3X2+kJQw2ngUMc2I4HYj0g8r95NegAV7fOtgV6h
XF0m5ptVAgMBAAECggEAYOslnhXATLKOyxFMfE5mMzKaKg0zEr0KGZd18qX9J/HI
EPt7PqKPchEojHA8fUnT0+AQ7kwywmD8W9dxScHOeLc+kU7tIdX0T3OIhsiymjfW
6y8bd568Y/tDMljK9mvgUSi3koxoMdN5HQXflbVDUjV5c0OVS1sxWMVjgKZ3vrKF
YZSQQIwVQRPFXGlpOYdMmqxkulNY4aLxY6WFAGtxExwVeW2HU7MpAKzg+G+nJTDg
OjAyipWw0jPe748869nEow6nkMYE3dGlMQaKJpLDy8px4dLFG7FXr5rhfHP0UxK8
wLW7F/3JNATqx1MbHtJvkTSiaFCNXrT9E4FapdtQFQKBgQDwwQOCqVuYR9Ws1VNO
aNUWPaTUk502m/msmKj4kYV21vFQYQ/U7D24OVB87qCpkASPAUAf7ma3018e0fzL
aHx2nCP/YuzM5wp3V2wPnff3X+9WsevvfgdBf51Myu81hfwH6zwcP+OJFX7OkAru
DTxXCiD75pHrFIJUVkb546oQBwKBgQDLY+0psJpoBrTFgrhC0rIwAN+jZ/nd3d9P
bcLCP213pgDaBdbKuiccJMAPaDZ5U1otlfloC8ChoPtDWIWeCmFqxYm5PMONUMWK
a/HX28HwQpEGvintKT7hLt88avDayZRIG5vbhlEIfP+1jj10c4L9dLNZJg////cr
t3bQzxrqwwKBgQDcL4qoW3/b3Ab+VPQlyBbqimJP0Nl98lT8l3oiK+UmdRqqarrx
/0XLUQ/d0ti5e2/P4lLlYUIsaXKbW22aEuuSBuedULpGBCA8WYYJGm6IngLlBUBE
/rxgGPiiHQ99ohaIn4mQRJYy76fT/0Ufxpci+66C87MMjutLesTbEm6czQKBgHvR
bHrZyVYU5w3qg3QiwllY3syiqsl3nc/D+TG53VFenNwde+JUqySF9uoqPidkJ9zi
lT/TD8UVtIEOLeHFXgLbGPnM1Rt9lZSsHEGVxh4W2CUrtWhsmJwLpdkpHVGEMCIp
tJtSzJgHSMBlRGJVQ+Q6nEhkVI87a2SQvuNlgXRzAoGBAKJMdHklmlzoZcmUB07V
OsVKXVMAYQa9U8H0Q4+fxEhyWPBTHBuODmu5Zdnq4gqLekN9TADixtKrqLxB6tpy
f7KPVfVkTbkzdAvrebYyZNhKcVSkBsUvmPKzRMLgvJ40BNGdD3iicaJuNER2JbU8
/No2EBuc68oX6yDX/F2rFOGm
-----END PRIVATE KEY-----
	';

    protected $password = 'thisismysecurepassword';

    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'eloquent-async-keys' => EloquentAsyncKeys::class,
        ];
    }

    public function testKeyGeneration()
    {
        $rsa = EloquentAsyncKeys::create();
        $this->assertStringContainsString('-----BEGIN PUBLIC KEY-----', $rsa->getPublicKey());
        $this->assertStringContainsString('-----BEGIN PRIVATE KEY-----', $rsa->getPrivateKey());

        $data = 'abc123';
        $encrypted = $rsa->encrypt($data);
        $decrypted = $rsa->decrypt($encrypted);
        $this->assertSame($data, $decrypted);
    }

    public function testKeyPopulation()
    {
        $rsa = EloquentAsyncKeys::setKeys($this->publicKey, $this->privateKey);
        $this->assertStringContainsString('-----BEGIN PUBLIC KEY-----', ltrim($rsa->getPublicKey()));
        $this->assertStringContainsString('-----BEGIN PRIVATE KEY-----', ltrim($rsa->getPrivateKey()));

        $data = 'abc123';
        $encrypted = $rsa->encrypt($data);
        $decrypted = $rsa->decrypt($encrypted);
        $this->assertSame($data, $decrypted);

        $data = 'abc123';
        $encrypted = $rsa->encrypt($data, true);
        $decrypted = $rsa->decrypt($encrypted, true);
        $this->assertSame($data, $decrypted);

        $this->expectException(Exception::class);
        $decrypted = $rsa->decrypt($encrypted);
    }

    public function testKeyPopulationPassworded()
    {
        $rsa = new Keys();
        $rsa->setKeys($this->publicKey, $this->privateKey, $this->password);
        $rsa->create(); // creates new keys, with the private key password-protected

        $data = 'abc123';
        $encrypted = $rsa->encrypt($data);
        $decrypted = $rsa->decrypt($encrypted);

        $this->assertSame($data, $decrypted);

        $rsa2 = new Keys();
        $rsa->setKeys($this->publicKey, $this->privateKey);

        //this should now throw an exception as there is no password
        $this->expectException(Exception::class);
        $decrypted = $rsa2->decrypt($encrypted);
    }

    public function testLongerThanCanData()
    {
        $data = Str::random(255);
        $rsa = new Keys();
        $rsa->setKeys($this->publicKey, $this->privateKey, $this->password);
        $rsa->create(); // creates new keys, with the private key password-protected

        $this->expectException(MaxLengthException::class);
        $encrypted = $rsa->encrypt($data);
    }

    //EncryptionEngine
    public function testEncryptionEngineForLongerMessages()
    {
        $plaintext = Str::random(5990); //500 chars message - to long to deal with under openssl standards!!!

        // Setup our secured public / private keypair
        $rsa = new Keys();
        $rsa->setPassword(null)->create(); // creates new keys, with the private key password-protected

        $engine = new EncryptionEngine();

        $encrypted = $engine->encryptMessage($plaintext, $rsa->getPublicKey());

        $decrypted = $engine->decryptMessage($encrypted, $rsa->getDecryptedPrivateKey());

        $this->assertSame($plaintext, $decrypted);
    }

    public function testEncryptionEngineForLongerMessagesWithEncryptedKey()
    {
        $plaintext = Str::random(5990); //500 chars message - to long to deal with under openssl standards!!!

        // Setup our secured public / private keypair
        $rsa = new Keys();
        $rsa->setPassword($this->password)->create(); // creates new keys, with the private key password-protected

        $engine = new EncryptionEngine();

        $encrypted = $engine->encryptMessage($plaintext, $rsa->getPublicKey());

        $decrypted = $engine->decryptMessage($encrypted, $rsa->getDecryptedPrivateKey());

        $this->assertSame($plaintext, $decrypted);
    }
}
