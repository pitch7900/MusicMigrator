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
 * Class ThrottleMiddlewareTest
 * @package hamburgscleanest\GuzzleAdvancedThrottle\Tests
 */
class ThrottleMiddlewareTest extends TestCase
{

    /** @test
     */
    public function requests_are_limited() : void
    {
        $host = 'www.test.de';
        $ruleset = new RequestLimitRuleset([
            $host => [
                [
                    'max_requests' => 1
                ]
            ]
        ]);
        $throttle = new ThrottleMiddleware($ruleset);
        $stack = new MockHandler([new Response(200), new Response(200)]);
        $client = new Client(['base_uri' => $host, 'handler' => $throttle->handle()($stack)]);

        $response = $client->request('GET', '/');
        static::assertEquals(200, $response->getStatusCode());

        $this->expectException(TooManyRequestsHttpException::class);
        $client->request('GET', '/');
    }

    /** @test
     */
    public function requests_are_limited_when_invoked() : void
    {
        $host = 'www.test.de';
        $ruleset = new RequestLimitRuleset([
            $host => [
                [
                    'max_requests' => 1
                ]
            ]
        ]);
        $throttle = new ThrottleMiddleware($ruleset);
        $stack = new MockHandler([new Response(200), new Response(200)]);
        $client = new Client(['base_uri' => $host, 'handler' => $throttle()($stack)]);

        $response = $client->request('GET', '/');
        static::assertEquals(200, $response->getStatusCode());

        $this->expectException(TooManyRequestsHttpException::class);
        $client->request('GET', '/');
    }

    /** @test
     */
    public function wildcards_are_matched() : void
    {
        $ruleset = new RequestLimitRuleset([
            'www.{subdomain}.test.com' => [
                [
                    'max_requests' => 1
                ]
            ]
        ]);
        $throttle = new ThrottleMiddleware($ruleset);
        $stack = new MockHandler([new Response(200), new Response(200)]);
        $client = new Client(['base_uri' => 'www.en.test.com', 'handler' => $throttle->handle()($stack)]);

        $response = $client->request('GET', '/');

        static::assertEquals(200, $response->getStatusCode());
        $this->expectException(TooManyRequestsHttpException::class);

        $client->request('GET', '/');
    }
}