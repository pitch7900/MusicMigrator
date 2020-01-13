<?php

namespace hamburgscleanest\GuzzleAdvancedThrottle\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use hamburgscleanest\GuzzleAdvancedThrottle\Middleware\ThrottleMiddleware;
use hamburgscleanest\GuzzleAdvancedThrottle\RequestLimitRuleset;
use PHPUnit\Framework\TestCase;

/**
 * Class ForceCacheTest
 * @package hamburgscleanest\GuzzleAdvancedThrottle\Tests
 */
class ForceCacheTest extends TestCase
{

    /** @test
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function requests_are_always_cached() : void
    {
        $host = 'www.test.de';
        $ruleset = new RequestLimitRuleset([
            $host => [
                [
                    'max_requests' => 2
                ]
            ]
        ], 'force-cache');
        $throttle = new ThrottleMiddleware($ruleset);
        $body1 = 'test1';
        $body2 = 'test2';
        $body3 = 'test3';
        $stack = new MockHandler([new Response(200, [], $body1), new Response(200, [], $body2), new Response(200, [], $body3)]);
        $client = new Client(['base_uri' => $host, 'handler' => $throttle->handle()($stack)]);

        static::assertEquals((string) $client->request('GET', '/')->getBody(), $body1);
        static::assertEquals((string) $client->request('GET', '/')->getBody(), $body1);
        static::assertEquals((string) $client->request('GET', '/')->getBody(), $body1);
    }
}
