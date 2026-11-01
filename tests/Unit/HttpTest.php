<?php

declare(strict_types=1);

namespace Kipchak\Driver\Http\Tests\Unit;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\HttpFactory;
use Kipchak\Driver\Http\Exceptions\InvalidConfiguration;
use Kipchak\Driver\Http\Http;
use Kipchak\Driver\Http\Library\Http as HttpClient;
use Kipchak\Driver\Http\Tests\Support\Clients;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;

final class HttpTest extends TestCase
{
    public function testWithoutAConfigFileGetReturnsLaravelsClientAsBefore(): void
    {
        $container = Clients::boot(null);

        $client = Http::get();
        $this->assertInstanceOf(HttpClient::class, $client);
        $this->assertSame($client, Http::get(), 'one shared client');
        $this->assertSame($client, $container->get('driver.http'), 'the container key existing code uses');
    }

    public function testWithoutTheConfigDriverGetReturnsLaravelsClientAsBefore(): void
    {
        Clients::boot(null, withConfigDriver: false);

        $this->assertInstanceOf(HttpClient::class, Http::get());
    }

    public function testAConfigWithoutADefaultStillGivesLaravelsClientAsDefault(): void
    {
        Clients::boot(['ai' => ['type' => 'psr18']]);

        $this->assertInstanceOf(HttpClient::class, Http::get());
        $this->assertInstanceOf(GuzzleClient::class, Http::get('ai'));
    }

    public function testNamedClientsOfEachType(): void
    {
        $container = Clients::boot([
            'default' => ['type' => 'laravel'],
            'ai' => ['type' => 'psr18', 'options' => ['timeout' => 120]],
            'factories' => ['type' => 'psr17'],
        ]);

        $this->assertInstanceOf(HttpClient::class, Http::get());
        $this->assertInstanceOf(ClientInterface::class, Http::get('ai'));
        $this->assertInstanceOf(HttpFactory::class, Http::get('factories'));
        $this->assertSame(Http::get('ai'), $container->get('drivers.http.ai'));
        $this->assertSame(Http::get('ai'), Http::get('ai'), 'built once and shared');
    }

    public function testTheDefaultCanBeAPsr18Client(): void
    {
        $container = Clients::boot(['default' => ['type' => 'psr18']]);

        $this->assertInstanceOf(GuzzleClient::class, Http::get());
        $this->assertSame(Http::get(), $container->get('driver.http'));
    }

    public function testLaravelClientsApplyTheirOptionsAndBaseUrl(): void
    {
        Clients::boot([
            'github' => [
                'type' => 'laravel',
                'base_url' => 'https://api.github.com',
                'options' => ['timeout' => 7, 'headers' => ['User-Agent' => 'kipchak-test']],
            ],
        ]);
        $github = Http::get('github');
        $github->fake();

        $github->get('/repos/mamluk/kipchak');

        $options = $github->createPendingRequest()->getOptions();
        $this->assertSame(7, $options['timeout']);
        $this->assertSame('kipchak-test', $options['headers']['User-Agent']);
        [$request] = $github->recorded()[0];
        $this->assertSame('https://api.github.com/repos/mamluk/kipchak', $request->url());
        $this->assertSame('kipchak-test', $request->header('User-Agent')[0]);
    }

    public function testLaravelClientsDoNotShareOptions(): void
    {
        Clients::boot(['slow' => ['type' => 'laravel', 'options' => ['timeout' => 99]]]);

        $this->assertSame(99, Http::get('slow')->createPendingRequest()->getOptions()['timeout']);
        $this->assertNotSame(99, Http::get()->createPendingRequest()->getOptions()['timeout'] ?? null);
    }

    public function testPsr18ClientsTakeGuzzleOptionsAndBaseUrl(): void
    {
        Clients::boot(['ai' => ['type' => 'psr18', 'base_url' => 'https://api.example.com', 'options' => ['timeout' => 120]]]);

        $client = Http::get('ai');
        $this->assertSame(120, $client->getConfig('timeout'));
        $this->assertSame('https://api.example.com', (string) $client->getConfig('base_uri'));
    }

    public function testTypedHelpers(): void
    {
        Clients::boot(['ai' => ['type' => 'psr18'], 'factories' => ['type' => 'psr17']]);

        $this->assertSame(Http::get('ai'), Http::psr18('ai'));
        $this->assertSame(Http::get('factories'), Http::psr17('factories'));
    }

    public function testTypedHelpersRefuseAClientOfAnotherType(): void
    {
        Clients::boot(['ai' => ['type' => 'psr18']]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("kipchak.http client 'ai' is of type 'psr18', not 'psr17'");
        Http::psr17('ai');
    }

    public function testTheDefaultIsLaravelSoPsr18WithoutANameIsRefused(): void
    {
        Clients::boot(null);

        $this->expectExceptionMessage("kipchak.http client 'default' is of type 'laravel', not 'psr18'");
        Http::psr18();
    }

    public function testAnUnknownNameIsRefused(): void
    {
        Clients::boot(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("kipchak.http has no client named 'github'");
        Http::get('github');
    }

    /**
     * @return iterable<string, array{array, string}>
     */
    public static function invalidConfigs(): iterable
    {
        yield 'not an array' => [['ai' => 'psr18'], "kipchak.http: ai must be an array with a 'type'"];
        yield 'missing type' => [['ai' => ['options' => []]], "kipchak.http: ai.type must be 'laravel', 'psr18' or 'psr17'"];
        yield 'unknown type' => [['ai' => ['type' => 'curl']], "ai.type must be 'laravel', 'psr18' or 'psr17'"];
        yield 'options as a list' => [['ai' => ['type' => 'psr18', 'options' => [120]]], 'ai.options must map Guzzle request option names'];
        yield 'relative base url' => [['gh' => ['type' => 'laravel', 'base_url' => '/v1']], 'gh.base_url must be an absolute URL'];
        yield 'psr17 with options' => [['f' => ['type' => 'psr17', 'options' => ['timeout' => 1]]], "f is a psr17 factory, which takes no 'options' or 'base_url'"];
        yield 'unknown key' => [['ai' => ['type' => 'psr18', 'timeout' => 5]], "ai has unknown key 'timeout'"];
        yield 'numeric name' => [[['type' => 'psr18']], '0 must be a client name'];
    }

    #[DataProvider('invalidConfigs')]
    public function testInvalidConfigFailsAtBoot(array $config, string $message): void
    {
        $this->expectException(InvalidConfiguration::class);
        $this->expectExceptionMessage($message);

        Clients::boot($config);
    }

    public function testRequestsFromOneClientShareAGuzzleHandlerSoConnectionsAreReused(): void
    {
        $handler = static function (\Illuminate\Http\Client\PendingRequest $request): mixed {
            return (new \ReflectionProperty($request, 'handler'))->getValue($request);
        };

        $client = new HttpClient();
        $first = $handler($client->acceptJson());
        $second = $handler($client->withToken('x')->timeout(2));

        $this->assertIsCallable($first);
        $this->assertSame($first, $second);
        $this->assertNotSame($first, $handler((new HttpClient())->acceptJson()), 'Each client has its own');
    }

    public function testFakesStillWorkWithTheSharedHandler(): void
    {
        $client = new HttpClient();
        $client->fake(['*' => ['ok' => true]]);

        $this->assertTrue($client->get('https://example.test/a')->json('ok'));
        $this->assertTrue($client->get('https://example.test/b')->json('ok'));
        $client->assertSentCount(2);
    }
}
