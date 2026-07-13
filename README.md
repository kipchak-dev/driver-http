# HTTP Driver for Kipchak

This driver is an HTTP client based on the illuminate/http package that ships with Laravel.

It is used to make HTTP requests within Kipchak.

## Requirements

- PHP 8.4 or 8.5
- [Kipchak](https://kipchak.dev) `^2 || ^3`
- `illuminate/http` `^13`

## Versions

- **2.0** — upgrades `illuminate/http` from 12.x to 13.x and adds PHP 8.5 support. The driver's public API is unchanged. Requires `kipchak/core: ^2 || ^3`.
- **1.x** — `illuminate/http` `^12`, `kipchak/core` `^2`.

See the [Kipchak upgrade guide](https://kipchak.dev/docs/upgrades) for details.

#### Composer Package 

`kipchak/driver-http`

#### Sample Config File:

This driver does not require any configuration.

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