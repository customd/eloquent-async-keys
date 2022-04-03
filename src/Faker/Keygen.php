<?php

namespace CustomD\EloquentAsyncKeys\Faker;

use Faker\Provider\Internet;
use CustomD\EloquentAsyncKeys\Keypair;

class Keygen extends Internet
{
    /**
     * generate keygen for faker
     *
     * @param string|null $password
     * @param string|null $salt
     * @param string $algo
     *
     * @return array<string,string>
     */
    public function keygenCollection(?string $password = null, ?string $salt = null, string $algo = 'AES128'): array
    {
        $rsa = new Keypair([
            'versions' => [
                'AES128' => 'AES128',
                'AES192' => 'AES192',
                'AES256' => 'AES256',
            ],
            'default'  => $algo,
        ]);

        if ($password === null) {
            $password = $this->generator->password();
        }

        $dataSet = [
            'password' => $password,
        ];
        $rsa->setPassword($password)->setSalt($salt)->create();
        $dataSet['salt'] = strval($rsa->getSalt());
        $dataSet['publicKey'] = strval($rsa->getPublicKey());
        $dataSet['privateKey'] = strval($rsa->getPrivateKey());

        return $dataSet;
    }
}
