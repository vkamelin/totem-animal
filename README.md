# Totem Animal

VK Mini App built with PHP 8.4+, Slim 4, PHP-DI, Illuminate Database, Phinx, Monolog, Symfony Validator, PHPStan, and PHP CS Fixer.

## Install

```bash
mkdir -p totem-animal
cd totem-animal
composer init --name="totem-animal/backend" --type=project --no-interaction
composer require slim/slim:^4.15 slim/psr7:^1.7 php-di/php-di:^7.1 php-di/slim-bridge:^3.4 illuminate/database:^12.0 robmorgan/phinx:^0.16 vlucas/phpdotenv:^5.6 monolog/monolog:^3.8 symfony/validator:^7.3 symfony/cache:^7.3 symfony/console:^7.3
composer require --dev phpstan/phpstan:^2.1 friendsofphp/php-cs-fixer:^3.70 phpunit/phpunit:^11.5
composer install --no-dev --optimize-autoloader
cp .env.example .env
```

## Scripts

```bash
composer analyse
composer cs:fix
composer test
composer migrate
composer migrate:rollback
composer dump
```
