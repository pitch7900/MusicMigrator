<?php

namespace hamburgscleanest\GuzzleAdvancedThrottle\Exceptions;

/**
 * Class MemcachedServersNotSetException
 * @package hamburgscleanest\GuzzleAdvancedThrottle\Exceptions
 */
class MemcachedServersNotSetException extends \RuntimeException
{

    /**
     * MemcachedServersNotSetException constructor.
     */
    public function __construct()
    {
        parent::__construct('Please set the servers for memcached.');
    }
}