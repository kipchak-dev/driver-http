<?php

namespace Kipchak\Driver\Http;

use Kipchak\Driver\Http\Library\Http;
use Kipchak\Core\Interfaces;
use Psr\Container\ContainerInterface;

class Driver implements Interfaces\Driver
{
    protected static ContainerInterface $container;

    public static function initialise(ContainerInterface $container): void
    {
        self::$container = $container;
        self::set();
    }

    public static function set(): void
    {
        self::$container->set(Http::class, new Library\Http());
    }

    public static function get(?string $configKey = null): Library\Http
    {
        return self::$container->get('driver.http');
    }

}