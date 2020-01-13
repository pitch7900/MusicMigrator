<?php

namespace hamburgscleanest\GuzzleAdvancedThrottle\Cache\Drivers;

use Illuminate\Filesystem\Filesystem;


/**
 * Class FileDriver
 * @package hamburgscleanest\GuzzleAdvancedThrottle\Cache\Drivers
 */
class FileDriver extends LaravelDriver
{

    protected function _setContainer() : void
    {
        $this->_container['files'] = new Filesystem();
    }
}