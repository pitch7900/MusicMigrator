<?php

namespace hamburgscleanest\GuzzleAdvancedThrottle\Tests;

use GuzzleHttp\Psr7\Request;
use hamburgscleanest\GuzzleAdvancedThrottle\RequestLimiter;
use hamburgscleanest\GuzzleAdvancedThrottle\RequestLimitGroup;
use PHPUnit\Framework\TestCase;

/**
 * Class RequestLimiterGroupTest
 * @package hamburgscleanest\GuzzleAdvancedThrottle\Tests
 */
class RequestLimitGroupTest extends TestCase
{

    /** @test */
    public function can_be_created_statically() : void
    {
        $requestLimitGroup = RequestLimitGroup::create();

        static::assertInstanceOf(RequestLimitGroup::class, $requestLimitGroup);
    }

    /** @test
     * @throws \Exception
     */
    public function can_add_request_limiters() : void
    {
        $requestLimitGroup = RequestLimitGroup::create();
        $requestLimitGroup->addRequestLimiter(new RequestLimiter('www.test'));

        static::assertEquals(1, $requestLimitGroup->getRequestLimiterCount());
    }

    /** @test
     * @throws \Exception
     */
    public function can_remove_request_limiters() : void
    {
        $requestLimiter = new RequestLimiter('www.test');

        $requestLimitGroup = RequestLimitGroup::create();
        $requestLimitGroup->addRequestLimiter($requestLimiter);
        $requestLimitGroup->removeRequestLimiter($requestLimiter);

        static::assertEquals(0, $requestLimitGroup->getRequestLimiterCount());
    }

    /** @test
     * @throws \Exception
     */
    public function can_request_is_correct() : void
    {
        $host = 'http://www.test.com';
        $interval = 100;
        $requestLimitGroup = RequestLimitGroup::create();
        $requestLimitGroup->addRequestLimiter(new RequestLimiter($host, 1, $interval));
        $request = new Request('GET', $host . '/check');

        static::assertTrue($requestLimitGroup->canRequest($request));
        static::assertFalse($requestLimitGroup->canRequest($request));
    }

    /** @test
     * @throws \Exception
     */
    public function retry_seconds_are_correct() : void
    {
        $host = 'http://www.test.com';
        $interval = 100;
        $requestLimitGroup = RequestLimitGroup::create();

        static::assertEquals(0, $requestLimitGroup->getRetryAfter());

        $requestLimitGroup->addRequestLimiter(new RequestLimiter($host, 0, $interval));
        $requestLimitGroup->canRequest(new Request('GET', $host . '/check'));

        static::assertEquals($interval, $requestLimitGroup->getRetryAfter());
    }

}