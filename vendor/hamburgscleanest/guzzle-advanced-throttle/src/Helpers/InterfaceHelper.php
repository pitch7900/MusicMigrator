<?php

namespace hamburgscleanest\GuzzleAdvancedThrottle\Helpers;

/**
 * Class InterfaceHelper
 * @package hamburgscleanest\GuzzleAdvancedThrottle\src\Helpers
 */
class InterfaceHelper
{

    /**
     * Get every class that implements $interfaceName
     *
     * @param string $interfaceName
     * @return array
     */
    public static function getImplementations(string $interfaceName) : array
    {
        return \array_filter(\get_declared_classes(), function($className) use ($interfaceName)
        {
            return self::implementsInterface($className, $interfaceName);
        });
    }

    /**
     * Returns true|false if the $implementerClassName implements interface $interfaceName
     *
     * @param string $implementerClassName class name of the implementation class to test
     * @param string $interfaceName name of the interface that should be implemented
     * @return bool TRUE if the $implementerClassName implements $interfaceName, FALSE otherwise
     */
    public static function implementsInterface(string $implementerClassName, string $interfaceName) : bool
    {
        return \in_array($interfaceName, \class_implements($implementerClassName), true);
    }
}