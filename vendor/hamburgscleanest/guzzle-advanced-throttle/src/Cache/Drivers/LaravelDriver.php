<?php

namespace hamburgscleanest\GuzzleAdvancedThrottle\Cache\Drivers;

use Illuminate\Container\Container;


/**
 * Class LaravelDriver
 * @package hamburgscleanest\GuzzleAdvancedThrottle\Cache\Drivers
 */
abstract class LaravelDriver
{

    /** @var string */
    private const DEFAULT_CACHE_PREFIX = 'throttle_cache';
    /** @var Container */
    protected $_container;
    /** @var string */
    protected $_driver;
    /** @var array */
    protected $_options;
    /** @var string */
    private $_driverStoreKey;

    /**
     * LaravelDriver constructor.
     * @param string $driver
     * @param array $options
     */
    public function __construct(string $driver, array $options = [])
    {
        $this->_container = new Container();
        $this->_driver = $driver;
        $this->_driverStoreKey = 'cache.stores.' . $this->_driver;
        $this->_options = $options;
    }

    /**
     * @return Container
     */
    public function getContainer() : Container
    {
        $this->_setConfig();
        $this->_setContainer();

        return $this->_container;
    }

    abstract protected function _setContainer() : void;

    private function _setConfig() : void
    {
        $this->_container['config'] = [
            'cache.default'        => $this->_driver,
            $this->_driverStoreKey => ['driver' => $this->_driver] + $this->_options,
            'cache.prefix'         => $this->_options['cache_prefix'] ?? self::DEFAULT_CACHE_PREFIX
        ];
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    protected function _setStoreValue(string $key, $value) : void
    {
        $this->_container->offsetSet('config.' . $this->_driverStoreKey . '.' . $key, $value);
    }
}