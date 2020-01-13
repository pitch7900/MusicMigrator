<?php

namespace hamburgscleanest\GuzzleAdvancedThrottle\Tests;

use hamburgscleanest\GuzzleAdvancedThrottle\Cache\Helpers\CacheConfigHelper;
use hamburgscleanest\GuzzleAdvancedThrottle\Exceptions\LaravelCacheDriverNotSetException;
use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;

/**
 * Class CacheConfigHelperTest
 * @package hamburgscleanest\GuzzleAdvancedThrottle\Tests
 */
class CacheConfigHelperTest extends TestCase
{

    /** @test */
    public function throws_driver_not_set_exception() : void
    {
        $this->expectException(LaravelCacheDriverNotSetException::class);

        CacheConfigHelper::getDriver(new Repository());
    }

    /** @test
     */
    public function gets_driver() : void
    {
        static::assertEquals('redis', CacheConfigHelper::getDriver($this->_getCacheConfig()));
    }

    /**
     * @return Repository
     */
    private function _getCacheConfig() : Repository
    {
        return new Repository($this->_getConfig()->get('cache'));
    }

    /**
     * @return Repository
     */
    private function _getConfig() : Repository
    {
        return new Repository(require 'config/app.php');
    }

    /** @test
     */
    public function gets_container() : void
    {
        $container = CacheConfigHelper::getContainer($this->_getCacheConfig());

        static::assertNotNull($container->offsetGet('redis'));
    }

    /** @test
     */
    public function gets_cache_manager() : void
    {
        $cacheManager = CacheConfigHelper::getCacheManager($this->_getCacheConfig());

        static::assertEquals('redis', $cacheManager->getDefaultDriver());
    }
}
