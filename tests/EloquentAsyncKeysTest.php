<?php

namespace CustomD\EloquentAsyncKeys\Tests;

use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase;
use CustomD\EloquentAsyncKeys\ServiceProvider;
use CustomD\EloquentAsyncKeys\Exceptions\Exception;
use CustomD\EloquentAsyncKeys\Exceptions\InvalidKeysException;
use CustomD\EloquentAsyncKeys\Facades\EloquentAsyncKeys;

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

    protected $salt = 'thisisasalt';

    protected $versions = [
        'AES128' => 'AES128',
        'AES192' => 'AES192',
        'AES256' => 'AES256',
    ];

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
        $rsa = EloquentAsyncKeys::reset()->create();
        $this->assertStringContainsString('-----BEGIN PUBLIC KEY-----', $rsa->getPublicKey());
        $this->assertStringContainsString('-----BEGIN PRIVATE KEY-----', $rsa->getPrivateKey());

        $plainText = Str::random(150);
        foreach ($this->versions as $version => $algo) {
            $encrypted = $rsa->encrypt($plainText, $version);

            $cipherText = $encrypted['cipherText'];
            $key = $encrypted['keys'][0];

            $decrypted = $rsa->decrypt($cipherText, $key);
            $this->assertSame($plainText, $decrypted);
        }
    }

    public function testKeyGenerationWithPassword()
    {
        $rsa = EloquentAsyncKeys::setPassword($this->password)->create();
        $this->assertStringContainsString('-----BEGIN PUBLIC KEY-----', $rsa->getPublicKey());
        $this->assertStringContainsString('-----BEGIN ENCRYPTED PRIVATE KEY-----', $rsa->getPrivateKey());

        $plainText = Str::random(150);
        foreach ($this->versions as $version => $algo) {
            $encrypted = $rsa->encrypt($plainText, $version);
            $cipherText = $encrypted['cipherText'];
            $key = $encrypted['keys'][0];

            $decrypted = $rsa->decrypt($cipherText, $key);
            $this->assertSame($plainText, $decrypted);
        }
    }

    public function testKeyGenerationWithPasswordAndSalt()
    {
        $rsa = EloquentAsyncKeys::setPassword($this->password)->setSalt(true)->create();
        $this->assertStringContainsString('-----BEGIN PUBLIC KEY-----', $rsa->getPublicKey());
        $this->assertStringContainsString('-----BEGIN ENCRYPTED PRIVATE KEY-----', $rsa->getPrivateKey());
        $this->assertIsString($rsa->getSalt());

        $plainText = Str::random(150);
        foreach ($this->versions as $version => $algo) {
            $encrypted = $rsa->encrypt($plainText, $version);
            $cipherText = $encrypted['cipherText'];
            $key = $encrypted['keys'][0];

            $decrypted = $rsa->decrypt($cipherText, $key);
            $this->assertSame($plainText, $decrypted);
        }
    }

    public function testKeyPopulation()
    {
        $rsa = EloquentAsyncKeys::setKeys($this->publicKey, $this->privateKey);
        $this->assertStringContainsString('-----BEGIN PUBLIC KEY-----', ltrim($rsa->getPublicKey()));
        $this->assertStringContainsString('-----BEGIN PRIVATE KEY-----', ltrim($rsa->getPrivateKey()));

        $plainText = Str::random(150);
        foreach ($this->versions as $version => $algo) {
            $encrypted = $rsa->encrypt($plainText, $version);
            $cipherText = $encrypted['cipherText'];
            $key = $encrypted['keys'][0];

            $decrypted = $rsa->decrypt($cipherText, $key);
            $this->assertSame($plainText, $decrypted);
        }

        $this->expectException(Exception::class);
        $decrypted = $rsa->decrypt($cipherText, false);
    }

    public function testKeyPopulationWithPassword()
    {
        $rsa = EloquentAsyncKeys::setKeys($this->publicKey, $this->privateKey, $this->password);
        $rsa->create(); // creates new keys, with the private key password-protected

        $plainText = Str::random(150);
        foreach ($this->versions as $version => $algo) {
            $encrypted = $rsa->encrypt($plainText, $version);
            $cipherText = $encrypted['cipherText'];
            $key = $encrypted['keys'][0];

            $decrypted = $rsa->decrypt($cipherText, $key);
            $this->assertSame($plainText, $decrypted);
        }

        $rsa2 = EloquentAsyncKeys::setKeys($this->publicKey, $this->privateKey);

        //this should now throw an exception as there is no password
        $this->expectException(Exception::class);
        $decrypted = $rsa2->decrypt($cipherText, $key);
    }

    public function testKeyPopulationWithPasswordAndSalt()
    {
        $rsa = EloquentAsyncKeys::setKeys($this->publicKey, $this->privateKey, $this->password, $this->salt);
        $rsa->create(); // creates new keys, with the private key password-protected

        $plainText = Str::random(150);
        foreach ($this->versions as $version => $algo) {
            $encrypted = $rsa->encrypt($plainText, $version);
            $cipherText = $encrypted['cipherText'];
            $key = $encrypted['keys'][0];

            $decrypted = $rsa->decrypt($cipherText, $key);
            $this->assertSame($plainText, $decrypted);
        }

        $lockedKey = $rsa->getPrivateKey();

        $rsa2 = EloquentAsyncKeys::setKeys($this->publicKey, $lockedKey, $this->password, $this->salt);
        $decrypted = $rsa2->decrypt($cipherText, $key);
        $this->assertSame($plainText, $decrypted);

        //this should now throw an exception as there is no salt provided
        $this->expectException(Exception::class);
        $rsa3 = EloquentAsyncKeys::setKeys($this->publicKey, $lockedKey, $this->password);
        $decrypted = $rsa3->decrypt($cipherText, $key);
    }

    //EncryptionEngine
    public function testMessageEncryptionForLongerMessages()
    {
        // 100,000 chars should be a good test chars message...
        // It's too long to deal with under openssl standards!!!
        $plainText = Str::random(100000);

        // Setup our secured public / private keypair
        $rsa = EloquentAsyncKeys::create(); // creates new keys, with the private key password-protected

        foreach ($this->versions as $version => $algo) {
            $encrypted = $rsa->encrypt($plainText, $version);
            $cipherText = $encrypted['cipherText'];
            $key = $encrypted['keys'][0];

            $decrypted = $rsa->decrypt($cipherText, $key);
            $this->assertSame($plainText, $decrypted);
        }
    }

    public function testMessageEncryptionForLongerMessagesWithEncryptedKey()
    {
        // 100,000 chars should be a good test chars message...
        // It's too long to deal with under openssl standards!!!
        $plainText = Str::random(100000);

        // Setup our secured public / private keypair
        // creates new keys, with the private key password-protected
        $rsa = EloquentAsyncKeys::setPassword($this->password)->create();

        foreach ($this->versions as $version => $algo) {
            $encrypted = $rsa->encrypt($plainText, $version);
            $cipherText = $encrypted['cipherText'];
            $key = $encrypted['keys'][0];

            $decrypted = $rsa->decrypt($cipherText, $key);
            $this->assertSame($plainText, $decrypted);
        }
    }

    public function testShouldMatchConfigVars()
    {
        $config = app('config')['eloquent-async-keys'];
        $plainText = Str::random(10);
        $rsa = EloquentAsyncKeys::create(); // creates new keys, with the private key password-protected
        $encrypted = $rsa->encrypt($plainText);
        $cipherText = $encrypted['cipherText'];
        ['cipherText' => $cipherText, 'version' => $version, 'iv' => $iv] = $rsa->parseCipherData($cipherText);

        $this->assertSame((string) $version, (string) $config['default']);
    }

    public function testMultipleKeys()
    {
        $keys = [];
        $privates = [];
        for ($i = 1; $i < 20; $i += 2) {
            $key = EloquentAsyncKeys::reset()->create();
            $keys[$i] = $key->getPublicKey();
            $privates[$i] = $key->getDecryptedPrivateKey();
        }

        $plainText = Str::random(10);

        $encrypted = EloquentAsyncKeys::encryptWithKey($keys, $plainText);

        $cipherText = $encrypted['cipherText'];
        $cipherKeys = $encrypted['keys'];

        foreach ($privates as $uid => $privateKey) {
            $res = EloquentAsyncKeys::setPrivateKey($privateKey)->decrypt($cipherText, $cipherKeys[$uid]);
            $this->assertSame((string) $res, (string) $plainText);
        }
    }

    public function testMultipleKeysPreset()
    {
        $keys = [];
        $privates = [];

        $key = EloquentAsyncKeys::setKeys($this->publicKey, $this->privateKey);

        for ($i = 1; $i < 20; $i += 2) {
            $keys[$i] = $key->getPublicKey();
            $privates[$i] = $key->getDecryptedPrivateKey();
        }

        $plainText = Str::random(10);

        $encrypted = EloquentAsyncKeys::encryptWithKey($keys, $plainText);

        $cipherText = $encrypted['cipherText'];
        $cipherKeys = $encrypted['keys'];

        foreach ($privates as $uid => $privateKey) {
            $res = EloquentAsyncKeys::setPrivateKey($privateKey)->decrypt($cipherText, $cipherKeys[$uid]);
            $this->assertSame((string) $res, (string) $plainText);
        }
    }

    public function test_checkPublicKey_bad()
    {
        $this->expectException(InvalidKeysException::class);
        EloquentAsyncKeys::checkPublicKey([2 => 'bad key']);
    }

    public function test_checkPublicKey_good()
    {

        $r = EloquentAsyncKeys::checkPublicKey($this->publicKey);
        $this->assertNull($r);
    }

    public function testHiLoad()
    {
        for ($i = 0; $i < 100; $i++) {
            try {
                $this->testMultipleKeysPreset();
            } catch (\Exception $e) {
                dd($e);
            }
        }
    }
}
