<?php

namespace hamburgscleanest\GuzzleAdvancedThrottle\Helpers;

use Psr\Http\Message\ResponseInterface;

/**
 * Class ResponseHelper
 * @package hamburgscleanest\GuzzleAdvancedThrottle\Helpers
 */
class ResponseHelper
{

    /**
     * Did the request return a 4xx or 5xx status code?
     *
     * @param ResponseInterface $response
     * @return bool
     */
    public static function hasErrorStatusCode(ResponseInterface $response) : bool
    {
        return $response->getStatusCode() > 299;
    }
}
