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

### Register Facade (optional)

Register package facade in `config/app.php` in `aliases` section
```php
CustomD\EloquentAsyncKeys\Facades\EloquentAsyncKeys::class,
```

### Publish Configuration File & generate global key

```bash
php artisan vendor:publish --provider="CustomD\EloquentAsyncKeys\ServiceProvider" --tag="config"
php artisan asynckey
```

### Usage

**namespace CustomD\EloquentAsyncKeys;**


* **`EloquentAsyncKeys::create([$keySize = null], [$overwrite = false]): self`** - This allows you to create a new set of keys and returns an instance of the class.

* **`EloquentAsyncKeys::encrypt($data, [$version = null]): array`** - this allows you to encrypt a new message and optionally set then algorithm version
* **`EloquentAsyncKeys::encryptWithKey($publicKey, $data, [$version = null]): array`** - this allows you to encrypt a new message with a provided key and optionally set then algorithm version

* **`EloquentAsyncKeys::decrypt($data, [$key = null]): string`** - Decrypts the message
* **`EloquentAsyncKeys::decryptWithKey($privateKey, $data, $key = null): string`** - this allows you to decrypt a message with a provided key

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