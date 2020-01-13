<?php

namespace hamburgscleanest\GuzzleAdvancedThrottle\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use hamburgscleanest\GuzzleAdvancedThrottle\Middleware\ThrottleMiddleware;
use hamburgscleanest\GuzzleAdvancedThrottle\RequestLimitRuleset;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

/**
 * Class CacheTest
 * @package hamburgscleanest\GuzzleAdvancedThrottle\Tests
 */
class CacheTest extends TestCase
{

    /** @test
     */
    public function requests_are_cached() : void
    {
        $host = 'www.test.de';
        $ruleset = new RequestLimitRuleset([
            $host => [
                [
                    'max_requests' => 2
                ]
            ]
        ], 'cache');
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
     */
    public function throw_too_many_requests_when_nothing_in_cache() : void
    {
        $host = 'www.test.de';
        $ruleset = new RequestLimitRuleset([
            $host => [
                [
                    'max_requests' => 0
                ]
            ]
        ], 'cache');
        $throttle = new ThrottleMiddleware($ruleset);
        $stack = new MockHandler([new Response(200, [], 'test')]);
        $client = new Client(['base_uri' => $host, 'handler' => $throttle->handle()($stack)]);

        $this->expectException(TooManyRequestsHttpException::class);
        $client->request('GET', '/');
    }

    /** @test
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function order_of_parameters_is_irrelevant_when_values_are_the_same() : void
    {
        $host = 'www.test.de';
        $ruleset = new RequestLimitRuleset([
            $host => [
                [
                    'max_requests' => 1
                ]
            ]
        ], 'cache');
        $throttle = new ThrottleMiddleware($ruleset);
        $body1 = 'test1';
        $body2 = 'test2';
        $body3 = 'test3';
        $stack = new MockHandler([new Response(200, [], $body1), new Response(200, [], $body2), new Response(200, [], $body3)]);
        $client = new Client(['base_uri' => $host, 'handler' => $throttle->handle()($stack)]);

        $responses = [];
        $responses[] = (string) $client->request('GET', '?a=1&b=2&c=3')->getBody();
        $responses[] = (string) $client->request('GET', '?b=2&a=1&c=3')->getBody();
        $responses[] = (string) $client->request('GET', '?c=3&b=2&a=1')->getBody();

        static::assertEquals([$body1, $body1, $body1], $responses);
    }

    /** @test
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function unordered_parameters_with_different_values_are_not_the_same() : void
    {
        $host = 'www.test.de';
        $ruleset = new RequestLimitRuleset([
            $host => [
                [
                    'max_requests' => 1
                ]
            ]
        ], 'cache');
        $throttle = new ThrottleMiddleware($ruleset);
        $body1 = 'test1';
        $body2 = 'test2';
        $body3 = 'test3';
        $stack = new MockHandler([new Response(200, [], $body1), new Response(200, [], $body2), new Response(200, [], $body3)]);
        $client = new Client(['base_uri' => $host, 'handler' => $throttle->handle()($stack)]);

        $client->request('GET', '?a=1&b=2&c=3');

        $this->expectException(TooManyRequestsHttpException::class);
        $client->request('GET', '?b=1&a=2&c=3');
    }
}
