<?php

namespace CoonDesign\RateLimit;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class RateLimitMiddleware {

    protected $limitHandler = null;
    
    protected $maxRequests = 1;

    protected $seconds = 10;
    private Cache $cache;

    public function __construct() 
    {
        $this->cache = new Cache();
    }

    public function setRequestsPerSecond($maxRequests, $seconds)
    {
        if (!is_int($maxRequests)) throw new \InvalidArgumentException;
        if (!is_int($seconds)) throw new \InvalidArgumentException;

        $this->maxRequests = $maxRequests;
        $this->seconds = $seconds;
        return $this;
    }

    public function setHandler($handler)
    {
        $this->limitHandler = $handler;
        return $this;
    }

    protected function getRequestsCount($uniqueID)
    {
        $count = $this->cache->get($uniqueID);
        if (!$count) { $count = 0; }
        return intval($count);
    }

    public function __invoke(Request $request, RequestHandler $handle)
    {
        $response = $handle->handle($request);

        $uniqueID = $_SERVER['REMOTE_ADDR'];

        $requestsCount = $this->getRequestsCount($uniqueID);

        // If the request count is greater than the max allowed requests
        // then we need to return the handler response instead, which
        // will return in a 429 response, indicating the rate limit has been exceeded
        if ($requestsCount >= $this->maxRequests) {
            $handler = $this->limitHandler;
            return $handler($request, $response);
        }

        // Store the new request count
        $this->cache->set($uniqueID, $requestsCount+1, $this->seconds);
        return $response;
    }
}
