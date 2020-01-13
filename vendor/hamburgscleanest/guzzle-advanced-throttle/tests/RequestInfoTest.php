<?php

namespace hamburgscleanest\GuzzleAdvancedThrottle\Tests;

use DateTime;
use hamburgscleanest\GuzzleAdvancedThrottle\RequestInfo;
use PHPUnit\Framework\TestCase;

/**
 * Class RequestInfoTests
 * @package hamburgscleanest\GuzzleAdvancedThrottle\Tests
 */
class RequestInfoTest extends TestCase
{

    /** @test
     */
    public function can_be_created_statically() : void
    {
        $timestamp = (new DateTime())->getTimestamp();
        $requestCount = 15;
        $remainingSeconds = 60;
        $requestInfo = RequestInfo::create($requestCount, $timestamp, $remainingSeconds);

        static::assertEquals($requestCount, $requestInfo->requestCount);
        static::assertEquals($remainingSeconds, $requestInfo->remainingSeconds);
        static::assertEquals($timestamp, $requestInfo->expiresAt->getTimestamp());
    }
}