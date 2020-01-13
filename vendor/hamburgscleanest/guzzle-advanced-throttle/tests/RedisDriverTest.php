<?php

namespace hamburgscleanest\GuzzleAdvancedThrottle\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use hamburgscleanest\GuzzleAdvancedThrottle\Exceptions\RedisDatabaseNotSetException;
use hamburgscleanest\GuzzleAdvancedThrottle\Middleware\ThrottleMiddleware;
use hamburgscleanest\GuzzleAdvancedThrottle\RequestLimitRuleset;
use Illuminate\Config\Repository;
use Illuminate\Redis\RedisManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

/**
 * Class RedisDriverTest
 * @package hamburgscleanest\GuzzleAdvancedThrottle\Tests
 */
class RedisDriverTest extends TestCase
{

    /** @test
     */
    public function throws_database_not_set_exception() : void
    {
        $this->expectException(RedisDatabaseNotSetException::class);

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
                    'driver'  => 'redis',
                    'options' => []
                ]
            ]));
    }

    /** @test
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function requests_are_cached() : void
    {
        $database = [
            'cluster' => false,
            'default' => [
                'host'     => '127.0.0.1',
                'port'     => 6379,
                'database' => 0,
            ],
        ];
        $redis = new RedisManager(null, 'predis', $database);
        $redis->flushdb();

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
                    'driver'  => 'redis',
                    'options' => [
                        'database' => $database
                    ]
                ]
            ]));
        $throttle = new ThrottleMiddleware($ruleset);
        $body1 = 'test1';
        $body2 = 'test2';
        $body3 = 'test3';
        $stack = new MockHandler([new Response(200, [], $body1), new Response(200, [], $body2), new Response(200, [], $body3)]);
        $client = new Client(['base_uri' => $host, 'handler' => $throttle->handle()($stack)]);

        $responseOne = (string) $client->request('GET', '/')->getBody();
        $responseTwo = (string) $client->request('GET', '/')->getBody();
        $responseThree = (string) $client->request('GET', '/')->getBody();

        static::assertNotEquals($responseOne, $responseTwo);
        static::assertEquals($responseTwo, $responseThree);
    }

    /** @test
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function throw_too_many_requests_when_nothing_in_cache() : void
    {
        $database = [
            'cluster' => false,
            'default' => [
                'host'     => '127.0.0.1',
                'port'     => 6379,
                'database' => 0,
            ],
        ];
        $redis = new RedisManager(null, 'predis', $database);
        $redis->flushdb();

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
                    'driver'  => 'redis',
                    'options' => [
                        'database' => $database
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
