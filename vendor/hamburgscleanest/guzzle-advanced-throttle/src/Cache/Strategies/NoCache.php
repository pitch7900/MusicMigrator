<?php

namespace hamburgscleanest\GuzzleAdvancedThrottle\Cache\Strategies;

use GuzzleHttp\Promise\PromiseInterface;
use hamburgscleanest\GuzzleAdvancedThrottle\Cache\Interfaces\CacheStrategy;
use hamburgscleanest\GuzzleAdvancedThrottle\Cache\Interfaces\StorageInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Class NoCache
 * @package hamburgscleanest\GuzzleAdvancedThrottle\Cache\Strategies
 */
class NoCache implements CacheStrategy
{

    /**
     * NoCache constructor.
     * @param null|StorageInterface $storage
     */
    public function __construct(StorageInterface $storage = null)
    {
        // No caching, so don't do anything with the storage..
    }

    /**
     * @param RequestInterface $request
     * @param callable $handler
     * @return PromiseInterface
     */
    public function request(RequestInterface $request, callable $handler) : PromiseInterface
    {
        return $handler();
    }
}