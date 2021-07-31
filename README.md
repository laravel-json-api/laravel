![Tests](https://github.com/laravel-json-api/laravel/workflows/Tests/badge.svg)

# JSON:API for Web Artisans

Implement feature-rich [JSON:API](https://jsonapi.org) compliant APIs in your
[Laravel](https://laravel.com) applications. Build your next standards-compliant API today.

## Documentation

See our website, [laraveljsonapi.io](https://laraveljsonapi.io)

## Installation

Install using [Composer](https://getcomposer.org)

```bash
composer require laravel-json-api/laravel
```

See our documentation for further installation instructions.

## Upgrading

When upgrading you typically want to upgrade this package and all our related packages. This is the recommended way:

```bash
composer require laravel-json-api/laravel --no-update
composer require laravel-json-api/testing --dev --no-update
composer up laravel-json-api/* cloudcreativity/json-api-testing
```

## Example Application

To view an example Laravel application that uses this package, see the
[Dummy Application](https://github.com/laravel-json-api/laravel/tree/main/tests/dummy) within the tests folder.

## License

Laravel JSON:API is open-sourced software licensed under the [Apache 2.0 License](./LICENSE).
