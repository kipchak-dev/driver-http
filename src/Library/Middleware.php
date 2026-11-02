<?php

declare(strict_types=1);

namespace Kipchak\Driver\Http\Library;

use Psr\Http\Message\RequestInterface;

/**
 * Guzzle middleware applied to every request made through the HTTP driver: its Laravel clients, and so the drivers
 * built on them (OPA, Vault, ClickHouse, OAuth 2 Token and others), and its PSR-18 clients.
 *
 * For packages that act on all outgoing calls, such as the Circuit Breaker middleware:
 *
 *   Middleware::push('circuit-breaker', $guzzleMiddleware);
 *
 * Middleware is applied in the order pushed, the first outermost, and is read when each request is sent, so it
 * can be registered after clients have been created. Pushing a name again replaces it.
 */
final class Middleware
{
    /** @var array<string, callable(callable): callable> */
    private static array $middleware = [];

    /**
     * @param callable(callable): callable $middleware A Guzzle middleware.
     */
    public static function push(string $name, callable $middleware): void
    {
        unset(self::$middleware[$name]);
        self::$middleware[$name] = $middleware;
    }

    public static function remove(string $name): void
    {
        unset(self::$middleware[$name]);
    }

    /**
     * @return array<string, callable(callable): callable>
     */
    public static function all(): array
    {
        return self::$middleware;
    }

    /**
     * One Guzzle middleware that applies whatever is registered when each request is sent. For clients whose
     * handler stack is built once, such as the PSR-18 clients.
     *
     * @return callable(callable): callable
     */
    public static function dispatcher(): callable
    {
        return static fn (callable $handler): callable => static function (RequestInterface $request, array $options) use ($handler) {
            $next = $handler;
            foreach (array_reverse(self::$middleware) as $middleware) {
                $next = $middleware($next);
            }

            return $next($request, $options);
        };
    }
}
