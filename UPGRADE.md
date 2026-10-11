# Upgrade Guide

## From 2.x to 3.0

### Requirements

| | 2.x | 3.0 |
|---|---|---|
| PHP | >= 8.1 | ^8.2 (tested on 8.2 – 8.5) |
| Laravel | not declared (tested on 10.x) | 12.x or 13.x (Laravel 13 needs PHP >= 8.3) |
| spatie/laravel-data | ^3.0 \| ^4.0 | ^4.14 |
| guzzlehttp/guzzle | ^7.2 | no longer required directly (installed through `illuminate/http`) |

### Dropped

- PHP 8.1.
- Laravel 10 and 11 (both past end of security support). 3.0 now declares
  `illuminate/console`, `illuminate/http` and `illuminate/support` (`^12.0|^13.0`) explicitly,
  so Composer will refuse to install it on an older Laravel instead of failing at runtime.
- spatie/laravel-data 3.x.

### API changes

None. Public classes, method signatures and DTO properties under `src/` are unchanged.

### Steps

1. Make sure your application runs on PHP >= 8.2 and Laravel 12 or 13.
2. `composer require laravolt/camunda:^3.0`
3. If you also pin `spatie/laravel-data`, allow `^4.14` (Laravel 12) or `^4.20` (Laravel 13).

### For contributors

- The dev tool chain moved to PHPUnit 11.5 / 12 / 13 and orchestra/testbench 10 / 11;
  `brianium/paratest` and `sempro/phpunit-pretty-print` were removed.
- `composer test` runs the offline suite. Tests that need a Camunda server carry
  `#[Group('integration')]` and are skipped unless `CAMUNDA_URL` is set
  (`CAMUNDA_URL=... composer test:integration`).
- `phpunit.dist.xml` fails on deprecations, notices and warnings caused by `src/`.
