<?php

namespace hamburgscleanest\GuzzleAdvancedThrottle\Exceptions;

/**
 * Class LaravelCacheDriverNotSetException
 * @package hamburgscleanest\GuzzleAdvancedThrottle\Exceptions
 */
class LaravelCacheDriverNotSetException extends \RuntimeException
{

    /**
     * LaravelCacheDriverNotSetException constructor.
     */
    public function __construct()
    {
        parent::__construct('Laravel was enabled as the cache adapter but no driver was configured.');
    }
}