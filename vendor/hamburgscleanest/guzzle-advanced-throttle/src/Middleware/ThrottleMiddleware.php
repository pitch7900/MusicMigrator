<?php

namespace hamburgscleanest\GuzzleAdvancedThrottle\Middleware;

use hamburgscleanest\GuzzleAdvancedThrottle\RequestLimitRuleset;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

/**
 * Class ThrottleMiddleware
 * @package hamburgscleanest\GuzzleAdvancedThrottle\Middleware
 */
class ThrottleMiddleware
{

    /** @var RequestLimitRuleset */
    private $_requestLimitRuleset;

    /**
     * ThrottleMiddleware constructor.
     * @param RequestLimitRuleset $requestLimitRuleset
     */
    public function __construct(RequestLimitRuleset $requestLimitRuleset)
    {
        $this->_requestLimitRuleset = $requestLimitRuleset;
    }

    /**
     * @return callable
     * @throws \Exception
     */
    public function __invoke() : callable
    {
        return $this->handle();
    }

    /**
     * @return callable
     * @throws \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException
     * @throws \Exception
     */
    public function handle() : callable
    {
        return function(callable $handler) : callable
        {
            return function(RequestInterface $request, array $options) use ($handler)
            {
                return $this->_requestLimitRuleset->cache($request, $this->_requestHandler($handler, $request, $options));
            };
        };
    }

    /**
     * @param callable $handler
     * @param RequestInterface $request
     * @param array $options
     * @return callable
     * @throws \Exception
     */
    private function _requestHandler(callable $handler, RequestInterface $request, array $options) : callable
    {
        return function() use ($handler, $request, $options)
        {
            $requestLimitGroup = $this->_requestLimitRuleset->getRequestLimitGroup();
            if (!$requestLimitGroup->canRequest($request, $options))
            {
                throw new TooManyRequestsHttpException(
                    $requestLimitGroup->getRetryAfter(),
                    'The rate limit was exceeded. Please try again later.'
                );
            }

            return $handler($request, $options);
        };
    }
}
