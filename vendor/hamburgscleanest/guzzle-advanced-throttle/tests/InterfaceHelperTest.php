<?php

namespace hamburgscleanest\GuzzleAdvancedThrottle\Tests;

use hamburgscleanest\GuzzleAdvancedThrottle\Cache\Interfaces\CacheStrategy;
use hamburgscleanest\GuzzleAdvancedThrottle\Cache\Interfaces\StorageInterface;
use hamburgscleanest\GuzzleAdvancedThrottle\Cache\Strategies\Cacheable;
use hamburgscleanest\GuzzleAdvancedThrottle\Helpers\InterfaceHelper;
use PHPUnit\Framework\TestCase;

/**
 * Class InterfaceHelperTest
 * @package hamburgscleanest\GuzzleAdvancedThrottle\Tests
 */
class InterfaceHelperTest extends TestCase
{

    /** @test
     */
    public function knows_when_class_implements_interface() : void
    {
        self::assertTrue(InterfaceHelper::implementsInterface(Cacheable::class, CacheStrategy::class));
        self::assertFalse(InterfaceHelper::implementsInterface(Cacheable::class, StorageInterface::class));
    }

    /** @test
     */
    public function gets_implementations() : void
    {
        self::assertGreaterThan(0, \count(InterfaceHelper::getImplementations(\DateTimeInterface::class)));
        self::assertCount(0, InterfaceHelper::getImplementations(DummyInterface::class));
    }

}