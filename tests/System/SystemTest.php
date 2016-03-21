<?php

/*
 * This file is part of the Symfony Http Cache Tagging package.
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DTL\Symfony\HttpCacheTagging\Tests\System;

use Doctrine\Common\Cache\ArrayCache;
use DTL\Symfony\HttpCacheTagging\Storage\DoctrineCache;
use DTL\Symfony\HttpCacheTagging\TaggingKernel;
use DTL\Symfony\HttpCacheTagging\TagManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpCache\HttpCache;
use Symfony\Component\HttpKernel\HttpCache\Store;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SystemTest extends \PHPUnit_Framework_TestCase
{
    private $filesystem;
    private $workspaceDir;

    public function setUp()
    {
        $this->filesystem = new Filesystem();
        $this->workspaceDir = __DIR__ . DIRECTORY_SEPARATOR . '_workspace';

        if ($this->filesystem->exists($this->workspaceDir)) {
            $this->filesystem->remove($this->workspaceDir);
        }
        $this->filesystem->mkdir($this->workspaceDir);

        $this->store = new Store($this->workspaceDir);
        $this->tagStorage = new DoctrineCache(new ArrayCache());
        $this->tagManager = new TagManager($this->tagStorage, $this->store);
        $this->application = new TestKernel();
        $this->httpCache = new HttpCache($this->application, $this->store, null, ['debug' => true]);
        $this->taggingKernel = new TaggingKernel($this->httpCache, $this->tagManager);
    }

    /**
     * It should cache pages using the underlying HTTP cache.
     */
    public function testSystem()
    {
        $request = Request::create('/', 'GET');
        $response = $this->taggingKernel->handle($request);

        $this->assertEquals('GET /: miss, store', $response->headers->get('x-symfony-cache'));
        $response = $this->taggingKernel->handle($request);

        $this->assertEquals('GET /: fresh', $response->headers->get('x-symfony-cache'));
    }

    /**
     * It should store tagged responses.
     */
    public function testStoreTaggedResponse()
    {
        $this->application->setResponse(Response::create('ok', 200, [
            'X-Cache-Tags' => json_encode(['one', 'two']),
        ]));

        $request = Request::create('/');
        $this->taggingKernel->handle($request);
        $response = $this->taggingKernel->handle($request);
        $expectedDigest = $response->headers->get('x-content-digest');

        $digests = $this->tagStorage->getCacheIds(['one']);

        $this->assertCount(1, $digests, 'There should be one cache entry associated with the tag.');
        $this->assertEquals($digests[0], $expectedDigest, 'The content digest should be that which was given in the response');
    }

    /**
     * It should accept invalidation requests.
     */
    public function testInvalidateRequest()
    {
        $this->application->setResponse(Response::create('ok', 200, [
            'X-Cache-Tags' => json_encode(['one', 'two']),
        ]));

        $request = Request::create('/');
        $this->taggingKernel->handle($request);

        $digests = $this->tagStorage->getCacheIds(['one']);
        $this->assertCount(1, $digests);

        $request = Request::create('/', 'POST');
        $request->headers->set('X-Cache-Invalidate-Tags', json_encode(['one']));
        $this->taggingKernel->handle($request);

        $digests = $this->tagStorage->getCacheIds(['one']);

        $this->assertCount(0, $digests, 'It should have purged the pages tagged "one"');
        $digests = $this->tagStorage->getCacheIds(['two']);
        $this->assertCount(1, $digests, 'It should not have purged the pages tagged "two"');
    }
}

class TestKernel implements HttpKernelInterface
{
    private $response;

    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        $response = $this->response ?: new Response('Hello World');
        $response->setMaxAge(60);
        $response->setPublic(true);

        return $response;
    }
}
