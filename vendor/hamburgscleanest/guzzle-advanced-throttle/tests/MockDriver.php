<?php

namespace hamburgscleanest\GuzzleAdvancedThrottle\Tests;

use hamburgscleanest\GuzzleAdvancedThrottle\Cache\Drivers\LaravelDriver;

/**
 * Class MockDriver
 * @package hamburgscleanest\GuzzleAdvancedThrottle\Tests
 */
class MockDriver extends LaravelDriver
{

    protected function _setContainer() : void
    {
        $this->_container['mock'] = 'test';
    }
}