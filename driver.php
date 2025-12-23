<?php

use Psr\Container\ContainerInterface;
use Kipchak\Driver\Http\Driver;

/**
 * @var $container ContainerInterface
 */

Driver::initialise($container);
Driver::set();