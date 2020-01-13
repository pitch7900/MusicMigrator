<?php

namespace hamburgscleanest\GuzzleAdvancedThrottle\Cache\Helpers;

use hamburgscleanest\GuzzleAdvancedThrottle\Cache\Drivers\FileDriver;
use hamburgscleanest\GuzzleAdvancedThrottle\Cache\Drivers\LaravelDriver;
use hamburgscleanest\GuzzleAdvancedThrottle\Cache\Drivers\MemcachedDriver;
use hamburgscleanest\GuzzleAdvancedThrottle\Cache\Drivers\RedisDriver;
use hamburgscleanest\GuzzleAdvancedThrottle\Exceptions\LaravelCacheDriverNotSetException;
use hamburgscleanest\GuzzleAdvancedThrottle\Exceptions\UnknownLaravelDriverException;
use Illuminate\Cache\CacheManager;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;

/**
 * Class CacheConfigHelper
 * @package hamburgscleanest\GuzzleAdvancedThrottle\Cache\Helpers
 */
class CacheConfigHelper
{

    /** @var array */
    private const DRIVERS = [
        'file'      => FileDriver::class,
        'redis'     => RedisDriver::class,
        'memcached' => MemcachedDriver::class
    ];

    /**
     * @param Repository $config
     * @return CacheManager
     * @throws \hamburgscleanest\GuzzleAdvancedThrottle\Exceptions\UnknownLaravelDriverException
     * @throws \hamburgscleanest\GuzzleAdvancedThrottle\Exceptions\LaravelCacheDriverNotSetException
     */
    public static function getCacheManager(Repository $config) : CacheManager
    {
        return new CacheManager(self::getContainer($config));
    }

    /**
     * @param Repository $config
     * @return Container
     * @throws \hamburgscleanest\GuzzleAdvancedThrottle\Exceptions\UnknownLaravelDriverException
     * @throws \hamburgscleanest\GuzzleAdvancedThrottle\Exceptions\LaravelCacheDriverNotSetException
     */
    public static function getContainer(Repository $config) : Container
    {
        $driverName = self::getDriver($config);
        $driverClass = self::_getDriverClass($driverName);

        /** @var LaravelDriver $driverClass */
        $driverClass = new $driverClass($driverName, $config['options'] ?? []);

        return $driverClass->getContainer();
    }

    /**
     * @param Repository $config
     * @return string
     * @throws \hamburgscleanest\GuzzleAdvancedThrottle\Exceptions\LaravelCacheDriverNotSetException
     */
    public static function getDriver(Repository $config) : string
    {
        $driver = $config->get('driver');
        if ($driver === null)
        {
            throw new LaravelCacheDriverNotSetException();
        }

        return $driver;
    }

    /**
     * @param string $driverName
     * @return string
     * @throws \hamburgscleanest\GuzzleAdvancedThrottle\Exceptions\UnknownLaravelDriverException
     */
    private static function _getDriverClass(string $driverName) : string
    {
        if (!isset(self::DRIVERS[$driverName]))
        {
            throw new UnknownLaravelDriverException($driverName, self::DRIVERS);
        }

        return self::DRIVERS[$driverName];
    }
}