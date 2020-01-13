<?php

namespace hamburgscleanest\GuzzleAdvancedThrottle\Exceptions;

/**
 * Class RedisDatabaseNotSetException
 * @package hamburgscleanest\GuzzleAdvancedThrottle\Exceptions
 */
class RedisDatabaseNotSetException extends \RuntimeException
{

    /**
     * RedisDatabaseNotSetException constructor.
     */
    public function __construct()
    {
        parent::__construct('Please set a database connection for redis.');
    }
}