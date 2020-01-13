<?php

namespace hamburgscleanest\GuzzleAdvancedThrottle\Cache;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

/**
 * Class CachedResponse
 * @package hamburgscleanest\GuzzleAdvancedThrottle\Cache
 */
class CachedResponse
{

    /** @var array */
    private $_headers;
    /** @var string */
    private $_body;
    /** @var string */
    private $_protocol;
    /** @var int */
    private $_statusCode;
    /** @var string */
    private $_reason;


    /**
     * CachedResponse constructor.
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->_headers = $response->getHeaders();
        $this->_body = (string) $response->getBody();
        $this->_protocol = $response->getProtocolVersion();
        $this->_statusCode = $response->getStatusCode();
        $this->_reason = $response->getReasonPhrase();
    }

    /**
     * @return Response
     */
    public function getResponse() : Response
    {
        return new Response(
            $this->_statusCode,
            $this->_headers,
            $this->_body,
            $this->_protocol,
            $this->_reason
        );
    }
}
