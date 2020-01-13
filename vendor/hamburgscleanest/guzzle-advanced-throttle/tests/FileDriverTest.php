<?php

namespace hamburgscleanest\GuzzleAdvancedThrottle\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use hamburgscleanest\GuzzleAdvancedThrottle\Middleware\ThrottleMiddleware;
use hamburgscleanest\GuzzleAdvancedThrottle\RequestLimitRuleset;
use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

/**
 * Class FileDriverTest
 * @package hamburgscleanest\GuzzleAdvancedThrottle\Tests
 */
class FileDriverTest extends TestCase
{

    private const CACHE_DIR = './cache';

    /**
     * @test
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
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
        ],
            'cache',
            'laravel',
            new Repository([
                'cache' => [
                    'driver'  => 'file',
                    'options' => [
                        'path' => self::CACHE_DIR
                    ]
                ]
            ]));
        $throttle = new ThrottleMiddleware($ruleset);
        $stack = new MockHandler([new Response(200, [], 'test1'), new Response(200, [], 'test2'), new Response(200, [], 'test3')]);
        $client = new Client(['base_uri' => $host, 'handler' => $throttle->handle()($stack)]);

        $responseOne = (string) $client->request('GET', '/')->getBody();
        $responseTwo = (string) $client->request('GET', '/')->getBody();
        $responseThree = (string) $client->request('GET', '/')->getBody();

        // dd($responseOne, $responseTwo, $responseThree);

        static::assertNotEquals($responseOne, $responseTwo);
        static::assertEquals($responseTwo, $responseThree);
    }

    /**
     * @test
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
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
        ],
            'cache',
            'laravel',
            new Repository([
                'cache' => [
                    'driver'  => 'file',
                    'options' => [
                        'path' => self::CACHE_DIR
                    ]
                ]
            ]));
        $throttle = new ThrottleMiddleware($ruleset);
        $stack = new MockHandler([new Response(200, [], 'test')]);
        $client = new Client(['base_uri' => $host, 'handler' => $throttle->handle()($stack)]);

        $this->expectException(TooManyRequestsHttpException::class);
        $client->request('GET', '/');
    }

    /**
     * Delete generated test files..
     */
    protected function tearDown() : void
    {
        $this->_deleteCachedFiles();
    }

    private function _deleteCachedFiles() : void
    {
        $filesystem = new Filesystem();
        $filesystem->deleteDirectory(self::CACHE_DIR);
    }

}
