<?php

namespace Mamluk\Kipchak\Driver\Http;

use Mamluk\Kipchak\Interfaces;
use Psr\Container\ContainerInterface;

class Driver implements Interfaces\Driver
{
    protected static ContainerInterface $container;

    public static function initialise(ContainerInterface $container): void
    {
        self::$container = $container;
    }

    public static function set(): void
    {
        self::$container->set('driver.http', function(ContainerInterface $c): Library\Http {
            return new Library\Http();
        });
    }

    public static function get(?string $configKey = null): Library\Http
    {
        return self::$container->get('driver.http');
    }

}