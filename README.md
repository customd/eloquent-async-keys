# Eloquent Async Keys

Package description: CHANGE ME




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
```

You will need to add a foreign key migration to your users table:
example :
1.  run `php artisan make:migration UserKeystore`
2.  update the generated file to read as per example below
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


## Usage

CHANGE ME

## Security

If you discover any security related issues, please email
instead of using the issue tracker.

## Credits

- [](https://github.com/custom-d/eloquent-async-keys)
- [All contributors](https://github.com/custom-d/eloquent-async-keys/graphs/contributors)

This package is bootstrapped with the help of
[melihovv/laravel-package-generator](https://github.com/melihovv/laravel-package-generator).
