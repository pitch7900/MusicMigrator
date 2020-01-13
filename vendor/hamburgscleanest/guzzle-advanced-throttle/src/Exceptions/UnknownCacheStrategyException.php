<?php

namespace hamburgscleanest\GuzzleAdvancedThrottle\Exceptions;

use hamburgscleanest\GuzzleAdvancedThrottle\Cache\Interfaces\CacheStrategy;
use hamburgscleanest\GuzzleAdvancedThrottle\Helpers\InterfaceHelper;

/**
 * Class UnknownCacheStrategyException
 * @package hamburgscleanest\GuzzleAdvancedThrottle\Exceptions
 */
class UnknownCacheStrategyException extends \RuntimeException
{

    /**
     * UnknownCacheStrategyException constructor.
     * @param string $cacheStrategy
     * @param array $additionalStrategies
     */
    public function __construct(string $cacheStrategy, array $additionalStrategies = [])
    {
        parent::__construct(
            'Unknown cache strategy "' . $cacheStrategy . '".' . \PHP_EOL .
            'Available adapters: ' . \implode(', ', $additionalStrategies + InterfaceHelper::getImplementations(CacheStrategy::class))
        );
    }
}