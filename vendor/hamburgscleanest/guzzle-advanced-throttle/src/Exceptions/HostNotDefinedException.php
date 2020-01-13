<?php

namespace hamburgscleanest\GuzzleAdvancedThrottle\Exceptions;

/**
 * Class HostNotDefinedException
 * @package hamburgscleanest\GuzzleAdvancedThrottle\Exceptions
 */
class HostNotDefinedException extends \RuntimeException
{

    /**
     * HostNotDefinedException constructor.
     */
    public function __construct()
    {
        parent::__construct('At least a host has to be defined.');
    }
}