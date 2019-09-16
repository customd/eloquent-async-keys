<?php

namespace CustomD\EloquentAsyncKeys\Model;

use Illuminate\Database\Eloquent\Model;

class RsaKeystore extends Model
{
    //set our table name
    protected $table = 'rsa_keystore';

    protected $fillable = [
        'public_key',
        'private_key',
    ];

    /**
     * reference our User Model.
     */
    public function user()
    {
        return $this->hasOne(config('auth.providers.users.model'), 'rsa_keystore_id');
    }
}
