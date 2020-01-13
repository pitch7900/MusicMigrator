<?php

namespace hamburgscleanest\GuzzleAdvancedThrottle\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use hamburgscleanest\GuzzleAdvancedThrottle\Exceptions\MemcachedServersNotSetException;
use hamburgscleanest\GuzzleAdvancedThrottle\Middleware\ThrottleMiddleware;
use hamburgscleanest\GuzzleAdvancedThrottle\RequestLimitRuleset;
use Illuminate\Cache\MemcachedConnector;
use Illuminate\Config\Repository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

/**
 * Class MemcachedDriverTest
 * @package hamburgscleanest\GuzzleAdvancedThrottle\Tests
 */
class MemcachedDriverTest extends TestCase
{

    /** @test
     * @throws \Exception
     */
    public function throws_servers_not_set_exception() : void
    {
        $this->expectException(MemcachedServersNotSetException::class);

        new RequestLimitRuleset([
            'www.test.de' => [
                [
                    'max_requests' => 2
                ]
            ]
        ],
            'cache',
            'laravel',
            new Repository([
                'cache' => [
                    'driver'  => 'memcached',
                    'options' => []
                ]
            ])
        );
    }

    /** @test
     */
    public function requests_are_cached() : void
    {
        $this->_memcachedRequired();

        $servers = [
            [
                'host'   => '127.0.0.1',
                'port'   => 11211,
                'weight' => 100,
            ],
        ];

        $memcached = new MemcachedConnector();
        $memcached = $memcached->connect($servers);
        $memcached->flush();

        $host = 'www.test.de';
        $ruleset = new RequestLimitRuleset([
            $host => [
                [
                    'max_requests' => 2
                ]
            ]
        ],
            'cache',
            'laravel',
            new Repository([
                'cache' => [
                    'driver'  => 'memcached',
                    'options' => [
                        'servers' => [
                            [
                                'host'   => '127.0.0.1',
                                'port'   => 11211,
                                'weight' => 100,
                            ],
                        ]
                    ]
                ]
            ]));
        $throttle = new ThrottleMiddleware($ruleset);
        $stack = new MockHandler([new Response(200, [], null, '1'), new Response(200, [], null, '2'), new Response(200, [], null, '3')]);
        $client = new Client(['base_uri' => $host, 'handler' => $throttle->handle()($stack)]);

        $responseOne = $client->request('GET', '/')->getProtocolVersion();
        $responseTwo = $client->request('GET', '/')->getProtocolVersion();
        $responseThree = $client->request('GET', '/')->getProtocolVersion();

        static::assertNotEquals($responseOne, $responseTwo);
        static::assertEquals($responseTwo, $responseThree);
    }

    private function _memcachedRequired() : void
    {
        if (!class_exists('Memcached'))
        {
            self::markTestSkipped('Memcached is required for this test.');
        }
    }

    /** @test
     */
    public function throw_too_many_requests_when_nothing_in_cache() : void
    {
        $this->_memcachedRequired();

        $servers = [
            [
                'host'   => '127.0.0.1',
                'port'   => 11211,
                'weight' => 100,
            ],
        ];

        $memcached = new MemcachedConnector();
        $memcached = $memcached->connect($servers);
        $memcached->flush();

        $host = 'www.test.de';
        $ruleset = new RequestLimitRuleset([
            $host => [
                [
                    'max_requests' => 0
                ]
            ]
        ],
            'cache',
            'laravel',
            new Repository([
                'cache' => [
                    'driver'  => 'memcached',
                    'options' => [
                        'servers' => $servers
                    ]
                ]
            ]));
        $throttle = new ThrottleMiddleware($ruleset);
        $stack = new MockHandler([new Response()]);
        $client = new Client(['base_uri' => $host, 'handler' => $throttle->handle()($stack)]);

        $this->expectException(TooManyRequestsHttpException::class);
        $client->request('GET', '/');
    }

}