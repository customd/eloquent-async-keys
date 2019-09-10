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

### Publish Configuration File

```bash
php artisan vendor:publish --provider="CustomD\EloquentAsyncKeys\ServiceProvider" --tag="config"
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
