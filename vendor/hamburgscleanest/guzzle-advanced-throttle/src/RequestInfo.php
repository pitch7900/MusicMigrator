<?php

namespace hamburgscleanest\GuzzleAdvancedThrottle;

use DateTime;

/**
 * Class RequestInfo
 * @package hamburgscleanest\GuzzleAdvancedThrottle
 */
class RequestInfo
{

    /** @var int */
    public $requestCount;
    /** @var DateTime */
    public $expiresAt;
    /** @var int */
    public $remainingSeconds;

    /**
     * RequestInfo constructor.
     * @param int $requestCount
     * @param int $expirationTimestamp
     * @param int $remainingSeconds
     */
    public function __construct(int $requestCount, int $expirationTimestamp, int $remainingSeconds)
    {
        $this->requestCount = $requestCount;
        $this->expiresAt = (new DateTime())->setTimestamp($expirationTimestamp);
        $this->remainingSeconds = $remainingSeconds;
    }

    /**
     * @param int $requestCount
     * @param int $expirationTimestamp
     * @param int $remainingSeconds
     * @return RequestInfo
     */
    public static function create(int $requestCount, int $expirationTimestamp, int $remainingSeconds) : RequestInfo
    {
        return new static($requestCount, $expirationTimestamp, $remainingSeconds);
    }
}