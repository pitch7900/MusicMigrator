<?php

namespace hamburgscleanest\GuzzleAdvancedThrottle\Cache\Adapters;

use DateInterval;
use DateTime;
use GuzzleHttp\Psr7\Response;
use hamburgscleanest\GuzzleAdvancedThrottle\Cache\CachedResponse;
use hamburgscleanest\GuzzleAdvancedThrottle\RequestInfo;
use Psr\Http\Message\ResponseInterface;
use Illuminate\Config\Repository;

/**
 * Class ArrayAdapter
 * @package hamburgscleanest\GuzzleAdvancedThrottle\Cache\Adapters
 */
class ArrayAdapter extends BaseAdapter
{

    /** @var string */
    private const RESPONSE_KEY = 'response';
    /** @var string */
    private const EXPIRATION_KEY = 'expires_at';
    /** @var array */
    private $_storage = [];

    /**
     * StorageInterface constructor.
     * @param Repository|null $config
     */
    public function __construct(?Repository $config = null)
    {
        if ($config === null)
        {
            return;
        }

        $this->_ttl = $config->get('cache.ttl', self::DEFAULT_TTL);
        $this->_allowEmptyValues = $config->get('cache.allow_empty', $this->_allowEmptyValues);
    }

    /**
     * @param string $host
     * @param string $key
     * @param int $requestCount
     * @param \DateTime $expiresAt
     * @param int $remainingSeconds
     */
    public function save(string $host, string $key, int $requestCount, DateTime $expiresAt, int $remainingSeconds) : void
    {
        $this->_storage[$host][$key] = RequestInfo::create($requestCount, $expiresAt->getTimestamp(), $remainingSeconds);
    }

    /**
     * @param string $host
     * @param string $key
     * @return RequestInfo|null
     */
    public function get(string $host, string $key) : ?RequestInfo
    {
        return $this->_storage[$host][$key] ?? null;
    }

    /**
     * @param ResponseInterface $response
     * @param string $host
     * @param string $path
     * @param string $key
     * @param int $expiresAt
     * @throws \Exception
     */
    protected function _saveResponse(ResponseInterface $response, string $host, string $path, string $key) : void
    {
        $this->_storage[self::STORAGE_KEY][$host][$path][$key] = [
            self::RESPONSE_KEY   => new CachedResponse($response),
            self::EXPIRATION_KEY => (new DateTime())->add(new DateInterval('PT' . $this->_ttl . 'M'))->getTimestamp()
        ];
    }

    /**
     * @param string $host
     * @param string $path
     * @param string $key
     * @return null|Response
     */
    protected function _getResponse(string $host, string $path, string $key) : ?Response
    {
        $response = $this->_storage[self::STORAGE_KEY][$host][$path][$key] ?? null;

        if ($response !== null)
        {
            if ($response[self::EXPIRATION_KEY] > \time())
            {
                /** @var CachedResponse|null $cachedResponse */
                $cachedResponse = $response[self::RESPONSE_KEY];

                return $cachedResponse ? $cachedResponse->getResponse() : null;
            }

            $this->_invalidate($host, $path, $key);
        }

        return null;
    }

    /**
     * @param string $host
     * @param string $path
     * @param string $key
     */
    private function _invalidate(string $host, string $path, string $key) : void
    {
        unset($this->_storage[self::STORAGE_KEY][$host][$path][$key]);
    }
}
