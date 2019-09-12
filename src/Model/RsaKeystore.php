<?php

namespace CustomD\EloquentAsyncKeys\Model;

use Illuminate\Database\Eloquent\Model;

class RsaKeystore extends Model
{
	//
	protected $table = 'rsa_keystore';


	public function user()
    {
        return $this->hasOne(config('auth.providers.users.model'), 'rsa_keystore_id');
    }
}
