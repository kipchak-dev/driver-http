<?php

namespace Kipchak\Driver\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\HttpFactory;
use Kipchak\Core\Interfaces;
use Kipchak\Driver\Config\Config;
use Kipchak\Driver\Http\Config\ClientConfig;
use Kipchak\Driver\Http\Config\ClientType;
use Kipchak\Driver\Http\Exceptions\InvalidConfiguration;
use Kipchak\Driver\Http\Library\Http as HttpClient;
use Kipchak\Driver\Http\Library\Middleware;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;

/**
 * HTTP clients, by name, from config/kipchak.http.php:
 *
 *   return [
 *       'default' => ['type' => 'laravel'],
 *       'ai' => ['type' => 'psr18', 'options' => ['timeout' => 120]],
 *       'psr17' => ['type' => 'psr17'],
 *   ];
 *
 * Without the file, or without a 'default' entry, Http::get() returns Laravel's client, as it always has.
 *
 * Each client is built once per worker and shared: Laravel's client and Guzzle's keep no per-request state,
 * so this is safe in FrankenPHP worker mode, and connections are reused across requests.
 */
class Http implements Interfaces\Driver
{
    public const string DEFAULT = 'default';

    /** The container key Http::get() has always used; it holds the default client. */
    public const string LEGACY_KEY = 'driver.http';

    protected static ContainerInterface $container;

    /** @var array<string, ClientConfig> */
    private static array $clients = [];

    public static function initialise(ContainerInterface $container, ?string $configFileName = null): void
    {
        self::$container = $container;
        self::set($configFileName);
    }

    public static function set(?string $configFileName = null): void
    {
        $config = self::readConfig($configFileName ?? 'kipchak.http');

        self::$clients = [];
        foreach ($config as $name => $clientConfig) {
            if (!is_string($name) || $name === '') {
                throw InvalidConfiguration::at((string) $name, 'must be a client name');
            }
            self::$clients[$name] = ClientConfig::fromArray($name, $clientConfig);
        }
        self::$clients[self::DEFAULT] ??= new ClientConfig(self::DEFAULT, ClientType::Laravel);

        foreach (self::$clients as $name => $client) {
            self::$container->set(self::key($name), static fn (ContainerInterface $c) => self::build($client));
        }
        self::$container->set(self::LEGACY_KEY, static fn (ContainerInterface $c) => $c->get(self::key(self::DEFAULT)));
    }

    /**
     * The named client, or the default one: Laravel's client, a PSR-18 client or the PSR-17 factories,
     * depending on its 'type' in kipchak.http.
     */
    public static function get(?string $configKey = null): HttpClient|ClientInterface|HttpFactory
    {
        $name = $configKey ?? self::DEFAULT;
        if (!isset(self::$clients[$name])) {
            throw new \InvalidArgumentException("kipchak.http has no client named '{$name}'");
        }

        return self::$container->get(self::key($name));
    }

    /**
     * Http::get() for a client configured with 'type' => 'psr18', typed for editors and static analysis.
     */
    public static function psr18(?string $configKey = null): ClientInterface
    {
        return self::typed($configKey, ClientType::Psr18);
    }

    /**
     * Http::get() for a client configured with 'type' => 'psr17', typed for editors and static analysis.
     */
    public static function psr17(?string $configKey = null): HttpFactory
    {
        return self::typed($configKey, ClientType::Psr17);
    }

    private static function typed(?string $configKey, ClientType $expected): mixed
    {
        $name = $configKey ?? self::DEFAULT;
        $client = self::$clients[$name] ?? null;
        if ($client !== null && $client->type !== $expected) {
            throw new \InvalidArgumentException(
                "kipchak.http client '{$name}' is of type '{$client->type->value}', not '{$expected->value}'"
            );
        }

        return self::get($name);
    }

    private static function build(ClientConfig $client): HttpClient|ClientInterface|HttpFactory
    {
        return match ($client->type) {
            ClientType::Laravel => self::laravel($client),
            ClientType::Psr18 => new GuzzleClient(self::withMiddleware(
                $client->baseUrl === null ? $client->options : ['base_uri' => $client->baseUrl] + $client->options
            )),
            ClientType::Psr17 => new HttpFactory(),
        };
    }

    /**
     * Guzzle options with the shared middleware (Library\Middleware) added to the handler stack: a new stack, or the
     * one given as 'handler'. A plain callable handler is left as it is.
     */
    private static function withMiddleware(array $options): array
    {
        $handler = $options['handler'] ?? null;
        if ($handler === null) {
            $handler = HandlerStack::create();
            $options['handler'] = $handler;
        }
        if ($handler instanceof HandlerStack) {
            $handler->push(Middleware::dispatcher(), 'kipchak.middleware');
        }

        return $options;
    }

    private static function laravel(ClientConfig $client): HttpClient
    {
        $http = new HttpClient();
        if ($client->options !== []) {
            $http->globalOptions($client->options);
        }
        if ($client->baseUrl !== null) {
            $http->withBaseUrl($client->baseUrl);
        }

        return $http;
    }

    private static function readConfig(string $configFileName): array
    {
        // The config driver is optional for this driver: without it, or without the file, only the default
        // Laravel client exists, as before.
        if (!class_exists(Config::class)) {
            return [];
        }

        try {
            return Config::get($configFileName);
        } catch (\Throwable) {
            return [];
        }
    }

    private static function key(string $name): string
    {
        return 'drivers.http.' . $name;
    }
}
