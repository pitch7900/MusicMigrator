<?php

namespace hamburgscleanest\GuzzleAdvancedThrottle\Cache\Interfaces;

use DateTime;
use hamburgscleanest\GuzzleAdvancedThrottle\RequestInfo;
use Illuminate\Config\Repository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface StorageInterface
 * @package hamburgscleanest\GuzzleAdvancedThrottle\Cache\Interfaces
 */
interface StorageInterface
{

    /**
     * StorageInterface constructor.
     * @param Repository|null $config
     */
    public function __construct(?Repository $config = null);

    /**
     * @param string $host
     * @param string $key
     * @param int $requestCount
     * @param DateTime $expiresAt
     * @param int $remainingSeconds
     */
    public function save(string $host, string $key, int $requestCount, DateTime $expiresAt, int $remainingSeconds) : void;

    /**
     * @param string $host
     * @param string $key
     * @return RequestInfo|null
     */
    public function get(string $host, string $key) : ?RequestInfo;

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function saveResponse(RequestInterface $request, ResponseInterface $response) : void;

    /**
     * @param RequestInterface $request
     * @return ResponseInterface|null
     */
    public function getResponse(RequestInterface $request) : ?ResponseInterface;
}