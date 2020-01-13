<?php

namespace hamburgscleanest\GuzzleAdvancedThrottle\Exceptions;

/**
 * Class LaravelCacheConfigNotSetException
 * @package hamburgscleanest\GuzzleAdvancedThrottle\Exceptions
 */
class LaravelCacheConfigNotSetException extends \RuntimeException
{

    /**
     * LaravelCacheConfigNotSetException constructor.
     */
    public function __construct()
    {
        parent::__construct('Laravel was enabled as the cache adapter but no config was found.');
    }
}