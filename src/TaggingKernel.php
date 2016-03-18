<?php

/*
 * This file is part of the Glob package.
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DTL\Symfony\HttpCacheTagging;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

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