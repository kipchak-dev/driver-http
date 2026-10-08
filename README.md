# HTTP Driver for Kipchak

This driver provides Kipchak's HTTP clients. By default, `Http::get()` returns Laravel's HTTP client
(`illuminate/http`). Named clients, configured in `config/kipchak.http.php`, can also be a PSR-18 client or
the PSR-17 factories, for libraries and SDKs that require the PHP standards.

## Requirements

- PHP 8.4 or 8.5
- [Kipchak](https://kipchak.dev) `^2 || ^3`
- `illuminate/http` `^13`

## Versions

- **2.10** — named clients from an optional `config/kipchak.http.php`, of type `laravel`, `psr18` or `psr17`,
  with `Http::get('<name>')`, `Http::psr18()` and `Http::psr17()`. Without the file nothing changes:
  `Http::get()` returns Laravel's client.
- **2.0** — upgrades `illuminate/http` from 12.x to 13.x and adds PHP 8.5 support. The driver's public API is unchanged. Requires `kipchak/core: ^2 || ^3`.
- **1.x** — `illuminate/http` `^12`, `kipchak/core` `^2`.

See the [Kipchak upgrade guide](https://kipchak.dev/docs/upgrades) for details.

#### Composer Package 

`kipchak/driver-http`

#### Sample Config File:

None is required. To define named clients, copy [`sample.config.php`](sample.config.php) to
`config/kipchak.http.php`:

```php
return [
    'default' => ['type' => 'laravel'],
    'github' => ['type' => 'laravel', 'base_url' => 'https://api.github.com', 'options' => ['timeout' => 10]],
    'ai' => ['type' => 'psr18', 'options' => ['timeout' => 120]],
    'psr17' => ['type' => 'psr17'],
];
```

`options` are Guzzle request options. A `psr18` client is `GuzzleHttp\Client` (PSR-18) and `psr17` is
`GuzzleHttp\Psr7\HttpFactory` (all six PSR-17 factories).

## How to use it?

Install it via composer: `composer require kipchak/driver-http`.

### Example Usage

```php
// Load the HTTP driver.
$http = Kipchak\Driver\Http\Http::get()
// Use the HTTP client ($http) client as you normally would.
```
See the methods available in the client here: https://laravel.com/docs/13.x/http-client. All methods
in the Laravel documentation called via `Http::method()` are available via `$http->method()` in Kipchak.

```php
$psr18 = Kipchak\Driver\Http\Http::psr18('ai');      // Psr\Http\Client\ClientInterface
$psr17 = Kipchak\Driver\Http\Http::psr17('psr17');   // GuzzleHttp\Psr7\HttpFactory
```

## Tests

```bash
composer install
vendor/bin/http_test_server &     # the PSR-18 suite's server, on 127.0.0.1:10000
./vendor/bin/phpunit
```

The `Compliance` suite runs the official PSR-18 (`php-http/client-integration-tests`) and PSR-17
(`http-interop/http-factory-tests`) tests against configured clients. Without the test server, the PSR-18
client tests are skipped; the feature tests need internet access. One feature test is replaced: following
redirects is an optional feature that PSR-18 does not specify, and the PSR-18 client returns the 3xx response.

## What is a Kipchak Driver?

Kipchak Drivers are used to connect Kipchak to various data sources or storage systems.

They provide a standardized interface for interacting with different data sources, allowing developers to
focus on building their applications rather than dealing with the complexities of each data source.

Drivers were introduced as a part of the Kipchak 2.0 release.

Drivers are basically Container Dependencies injected into Kipchak's Service Container.

## How do Kipchak Drivers work?

Some Kipchak drivers are wired into the Service Container via a config file (where required).

If applicable, you will find a sample config file in this repository as well as in the starter project at https://1x.ax/mamluk/kipchak/starter.

Each driver defines an implementation of Kipchak's Driver Interface. Where applicable, the dependency may
accept a parameter for a specific instance of the driver specified as a config property, for instance, in the case
of multiple database connections or S3 buckets.

So you may access the driver by invoking \Kipchak\Driver\Memcached\Memcached::get('cache'), where
`cache` is the name of one of the memcached connection pools specified in the config file.