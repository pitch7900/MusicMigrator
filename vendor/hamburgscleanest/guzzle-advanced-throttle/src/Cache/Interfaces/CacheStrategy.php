<?php

namespace hamburgscleanest\GuzzleAdvancedThrottle\Cache\Interfaces;

use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;


/**
 * Interface CacheStrategy
 * @package hamburgscleanest\GuzzleAdvancedThrottle\Cache\Interfaces
 */
interface CacheStrategy
{

    /**
     * CacheStrategy constructor.
     * @param null|StorageInterface $storage
     */
    public function __construct(StorageInterface $storage = null);

    /**
     * @param RequestInterface $request
     * @param callable $handler
     * @return PromiseInterface
     */
    public function request(RequestInterface $request, callable $handler) : PromiseInterface;
}