<?php

namespace DTL\Symfony\HttpCacheTagging;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use DTL\Symfony\HttpCacheTagging\TaggingHandler;

/**
 * Middleware for adding tagging support to the Symfony
 * HTTP cache.
 */
class TaggingKernel implements HttpKernelInterface
{
    private $kernel;
    private $handler;

    public function __construct(HttpKernelInterface $kernel, TagManager $tagManager)
    {
        $this->handler = new TaggingHandler($tagManager);
        $this->kernel = $kernel;
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        if ($response = $this->handler->handleRequest($request)) {
            return $response;
        }

        $response = $this->kernel->handle($request, $type, $catch);

        $this->handler->handleResponse($response);

        return $response;
    }
}
