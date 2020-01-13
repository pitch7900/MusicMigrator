<?php

namespace hamburgscleanest\GuzzleAdvancedThrottle\Exceptions;

/**
 * Class UnknownLaravelDriverException
 * @package hamburgscleanest\GuzzleAdvancedThrottle\Exceptions
 */
class UnknownLaravelDriverException extends \RuntimeException
{

    /**
     * UnknownLaravelDriverException constructor.
     * @param string $driverName
     * @param array $availableDrivers
     */
    public function __construct(string $driverName, array $availableDrivers)
    {
        parent::__construct('Unknown Laravel driver "' . $driverName . '".' . \PHP_EOL . 'Available drivers: ' . \implode(', ', $availableDrivers));
    }
}