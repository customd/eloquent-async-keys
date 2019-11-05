<?php

namespace CustomD\EloquentAsyncKeys\Faker;

use Faker\Provider\Internet;
use CustomD\EloquentAsyncKeys\Keypair;

class Keygen extends Internet
{
    public function keygenCollection($password = null, $salt = null, $algo = 'AES128')
    {
        $rsa = new Keypair([
            'versions' => [
                'AES128' => 'AES128',
                'AES192' => 'AES192',
                'AES256' => 'AES256',
            ],
            'default' => $algo,
        ]);

        if ($password === null) {
            $password = $this->generator->password();
        }

        $dataSet = [
            'password' => $password,
        ];
        $rsa->setPassword($password)->setSalt($salt)->create();
        $dataSet['salt'] = $rsa->getSalt();
        $dataSet['publicKey'] = $rsa->getPublicKey();
        $dataSet['privateKey'] = $rsa->getPrivateKey();

        return $dataSet;
    }
}
