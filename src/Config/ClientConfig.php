<?php

declare(strict_types=1);

namespace Kipchak\Driver\Http\Config;

use Kipchak\Driver\Http\Exceptions\InvalidConfiguration;

/**
 * One named client in kipchak.http:
 *
 *   'github' => ['type' => 'laravel', 'base_url' => 'https://api.github.com', 'options' => ['timeout' => 10]],
 *   'ai'     => ['type' => 'psr18', 'options' => ['timeout' => 120]],
 *   'psr17'  => ['type' => 'psr17'],
 *
 * 'options' are Guzzle request options (timeout, connect_timeout, headers, proxy, verify, ...), which both
 * Laravel's client and Guzzle's take. 'base_url' is prepended to relative URLs.
 */
final readonly class ClientConfig
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        public string $name,
        public ClientType $type,
        public array $options = [],
        public ?string $baseUrl = null,
    ) {
    }

    public static function fromArray(string $name, mixed $config): self
    {
        if (!is_array($config)) {
            throw InvalidConfiguration::at($name, "must be an array with a 'type', e.g. ['type' => 'laravel']");
        }

        $type = ClientType::tryFrom((string) ($config['type'] ?? ''))
            ?? throw InvalidConfiguration::at("{$name}.type", "must be 'laravel', 'psr18' or 'psr17'");

        $options = $config['options'] ?? [];
        if (!is_array($options) || ($options !== [] && array_is_list($options))) {
            throw InvalidConfiguration::at("{$name}.options", 'must map Guzzle request option names to values');
        }

        $baseUrl = $config['base_url'] ?? null;
        if ($baseUrl !== null && (!is_string($baseUrl) || filter_var($baseUrl, FILTER_VALIDATE_URL) === false)) {
            throw InvalidConfiguration::at("{$name}.base_url", 'must be an absolute URL');
        }

        if ($type === ClientType::Psr17 && ($options !== [] || $baseUrl !== null)) {
            throw InvalidConfiguration::at($name, "is a psr17 factory, which takes no 'options' or 'base_url'");
        }

        $unknown = array_diff(array_keys($config), ['type', 'options', 'base_url']);
        if ($unknown !== []) {
            throw InvalidConfiguration::at($name, "has unknown key '" . reset($unknown) . "'; use 'type', 'options' or 'base_url'");
        }

        return new self($name, $type, $options, $baseUrl);
    }
}
