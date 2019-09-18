<?php

namespace CustomD\EloquentAsyncKeys\Faker;

use Faker\Provider\Internet;
use CustomD\EloquentAsyncKeys\Keys;

class Keygen extends Internet
{
    public function keygenCollection($password = null, $salt = null)
    {
        $rsa = new Keys();

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
