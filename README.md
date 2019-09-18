# Eloquent Async Keys

Package to handle public private keys for your application and other uses, stored in a `rsa_keystore` model which can be linked to any other model.

## Installation

Install via composer
```bash
composer require custom-d/eloquent-async-keys
```

### Register Service Provider

**Note! This and next step are optional if you use laravel>=5.5 with package
auto discovery feature.**

Add service provider to `config/app.php` in `providers` section
```php
CustomD\EloquentAsyncKeys\ServiceProvider::class,
```

### Register Facade

Register package facade in `config/app.php` in `aliases` section
```php
CustomD\EloquentAsyncKeys\Facades\EloquentAsyncKeys::class,
```

### Publish Configuration File, Run migrations

```bash
php artisan vendor:publish --provider="CustomD\EloquentAsyncKeys\ServiceProvider" --tag="config"
php artisan migrate
php artisan asynckey
```

### Example Usage

Lets assign each user their own public private key

*You will need to add a foreign key migration to your users table:*

**example :**

1. run `php artisan make:migration UserKeystore`
2. update the generated file to read as per example below
```php
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserKeystore extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::table('users', function (Blueprint $table) {
			$table->unsignedBigInteger('rsa_keystore_id')->nullable();
			$table->foreign('rsa_keystore_id')->references('id')->on('rsa_keystore')->onDelete('CASCADE')->onUpdate('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		$table->dropColumn(['rsa_keystore_id']);
    }
}

```

3. Update your User Model and add the following method:
```php
use CustomD\EloquentAsyncKeys\Model\RsaKeystore;
...
	public function rsaKeys()
    {
        return $this->belongsTo(RsaKeystore::class, 'rsa_keystore_id');
    }
```


4. (optional) overwrite the default user map.
Create your own model to extend the `CustomD\EloquentAsyncKeys\Model\RsaKeystore` Model and in it overwrite the following method to map back to your users.
```php
	/**
     * reference our User Model.
     */
    public function user()
    {
        return $this->hasOne(config('auth.providers.users.model'), 'rsa_keystore_id');
    }

```

### Usage

**namespace CustomD\EloquentAsyncKeys;**


* **`EloquentAsyncKeys::create([$keySize = null], [$overwrite = false]): self`** - This allows you to create a new set of keys and returns an instance of the class.

* **`EloquentAsyncKeys::encrypt($data, [$encode = false]): string`** - this allows you to encrypt a new message and optionally base64_encode the encrypted data for storage
* **`EloquentAsyncKeys::encryptWithKey($publicKey, $data, [$encode = false]): string`** - this allows you to encrypt a new message with a provided key and optionally base64_encode the encrypted data for storage

* **`EloquentAsyncKeys::decrypt($data, [$decode = false]): string`** - Decrypts the message and optionally base64_decodes the encrypted data before decoding.
* **`EloquentAsyncKeys::decryptWithKey($privateKey, $data, $decode = false): string`** - this allows you to decrypt a message with a provided key and optionally base64_decode the encrypted data

* **`EloquentAsyncKeys::testIfStringIsToLong(string $string): void`** - Tests if the string is to long for the standard encryption by ssl key, will throw an `CustomD\EloquentAsyncKeys\Exceptions\MaxLengthException` if it does.


* **`EloquentAsyncKeys::getPublicKey(): string`** - gets the current public key
* **`EloquentAsyncKeys::getPrivateKey(): string`** - gets the current private key
* **`EloquentAsyncKeys::getSalt(): string`** - gets the current salt
* **`EloquentAsyncKeys::getDecryptedPrivateKey(): resource`** - gets the current decrypted private key

* **`EloquentAsyncKeys::setKeys([$publicKey = null], [$privateKey = null], [$password = null]): self`** - set the public, private and password
* **`EloquentAsyncKeys::setPublicKey(?string $publicKey = null): self`** - sets/unsets the public key
* **`EloquentAsyncKeys::setPrivateKey(?string $privateKey = null): self`** - sets/unsets the private key
* **`EloquentAsyncKeys::setSalt($salt = null): self`** - sets/unsets/generates salt, (pass true to generate a new salt)
* **`EloquentAsyncKeys::setPassword(?string $password = null): self`** - set/unset the password for the current private key

* **`EloquentAsyncKeys::setNewPassword(string $newPassword, $newSalt = false): void`** - sets a new password onto your current privateKey

**namespace CustomD\EloquentAsyncKeys;**
* **`MessageEncryption::encryptMessage($plainText, $publicKey, [$key = null])`** - USed to encrypt longer messages, piggy backs on Laravels Encryption engine and cipher `AES-128-CBC`, Key if provided should be a 16Byte key. else one will be magically genrated for you.

* **`MessageEncryption::decryptMessage($encryptedMessage, $privateKey)`** - decrypts the message using the privatekey provided and then decrypts using Laravels encryption engine the full message.


## Included for tinker / phpunit / seeds
We have added a faker library to use to populate your keystores:
```php
use CustomD\EloquentAsyncKeys\Faker\Keygen;
...
$faker = Factory::create(Factory::DEFAULT_LOCALE);
$faker->addProvider(new Keygen($faker));
$keyset = $faker->keygenCollection($faker->password(), true);
```
will return your with keyset as an array containing the following structure
```php
[
	'password' => x,
	'salt' => x,
	'publicKey' => x,
	'privateKey' => x
]
```
.
## Security

If you discover any security related issues, please email
instead of using the issue tracker.

## Credits

- [Custom D](https://github.com/custom-d/eloquent-async-keys)
- [All contributors](https://github.com/custom-d/eloquent-async-keys/graphs/contributors)