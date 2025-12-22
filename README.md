# HTTP Driver for Kipchak

This driver is an HTTP client based on the illuminate/http package that ships with Laravel.

It is used to make HTTP requests within Kipchak.

#### Composer Package 

`kipchak/driver-http`

#### Driver Name

`http.client`

#### Sample Config File:

This driver does not require any configuration.

## How to use it?

Install it via composer: `composer require kipchak/driver-http`.

The dependency name of this driver is `http.client`.

### Example Usage

```
$http = $this->container->get('http.client);
// Use the HTTP client ($http) client as you normally would.
```
See the methods available in the client here: https://laravel.com/docs/12.x/http-client. All methods
in the Laravel documentation called via `Http::method()` are available via `$http->method() in Kipchak.

## What is a Kipchak Driver?

Kipchak Drivers are used to connect Kipchak to various data sources or storage systems. 

They provide a standardized interface for interacting with different data sources, allowing developers to 
focus on building their applications rather than dealing with the complexities of each data source.

Drivers were introduced as a part of the Kipchak 2.0 release.

Drivers are basically Slim Dependecies injected into Kipchak's Service Container.

## How do Kipchak Drivers work?

Kipchak drivers are wired into the Service Container via a config file (where required).

Each driver has a pre-defined dependency name which may be sufficed with a config property, for instance, in the case 
of multiple database connections or S3 buckets.

So you may access the driver by invoking `$this->container->get('database.couchdb.default'), where 
`database.couchdb.` is the name of the driver and the `default` is the name of the connection specifiedin the config file.

**NOTE: This driver does not require any configuration.**