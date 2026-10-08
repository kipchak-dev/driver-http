<?php

declare(strict_types=1);

namespace Kipchak\Driver\Http\Tests\Support;

use Kipchak\Driver\Config\Config;
use Kipchak\Driver\Http\Http;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Boots the HTTP driver the way an API does, with a kipchak.http config (or none).
 */
final class Clients
{
    /**
     * @param array<string, mixed>|null $httpConfig The kipchak.http file's contents; null = no such file.
     */
    public static function boot(?array $httpConfig, bool $withConfigDriver = true): ContainerInterface
    {
        $container = new class implements ContainerInterface {
            private array $entries = [];
            private array $resolved = [];

            public function set(string $id, mixed $value): void
            {
                $this->entries[$id] = $value;
                unset($this->resolved[$id]);
            }

            public function get(string $id): mixed
            {
                if (!array_key_exists($id, $this->entries)) {
                    throw new class ("No entry {$id}") extends \RuntimeException implements NotFoundExceptionInterface {
                    };
                }
                if (!array_key_exists($id, $this->resolved)) {
                    $entry = $this->entries[$id];
                    // Like PHP-DI: a closure is a factory, resolved once and then shared.
                    $this->resolved[$id] = $entry instanceof \Closure ? $entry($this) : $entry;
                }

                return $this->resolved[$id];
            }

            public function has(string $id): bool
            {
                return array_key_exists($id, $this->entries);
            }
        };

        $configProperty = new \ReflectionProperty(Config::class, 'container');
        if ($withConfigDriver) {
            $container->set('drivers.config', $httpConfig === null ? [] : ['kipchak.http' => $httpConfig]);
            $configProperty->setValue(null, $container);
        } else {
            // An API that never initialised the config driver: any read of it fails.
            self::unsetStatic(Config::class, 'container');
        }

        Http::initialise($container);

        return $container;
    }

    private static function unsetStatic(string $class, string $property): void
    {
        // Typed static properties cannot be unset; give Config a container that has no config at all.
        $empty = new class implements ContainerInterface {
            public function get(string $id): mixed
            {
                throw new \Error('Config driver not initialised');
            }

            public function has(string $id): bool
            {
                return false;
            }
        };
        (new \ReflectionProperty($class, $property))->setValue(null, $empty);
    }
}
