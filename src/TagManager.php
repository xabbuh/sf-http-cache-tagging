<?php

namespace DTL\Symfony\HttpCacheTagging;

use DTL\Symfony\HttpCacheTagging\ManagerInterface;
use DTL\Symfony\HttpCacheTagging\StorageInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpCache\Store;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Tag manager for the Symfony HTTP Cache Proxy.
 */
class TagManager
{
    /**
     * @var StorageInterface
     */
    private $tagStorage;

    /**
     * @var Store
     */
    private $cacheStorage;

    public function __construct(StorageInterface $tagStorage, Store $cacheStorage, Filesystem $filesystem = null)
    {
        $this->tagStorage = $tagStorage;
        $this->cacheStorage = $cacheStorage;
        $this->filesystem = $filesystem ?: new Filesystem();
    }

    /**
     * Invalidate the cache entries associated with any of the given list of tags.
     *
     * @param string[] $tags
     */
    public function invalidateTags(array $tags)
    {
        $digests = $this->tagStorage->getCacheIds($tags);

        foreach ($digests as $cacheDigest) {
            $cachePath = $this->cacheStorage->getPath($cacheDigest);

            $this->filesystem->remove($cachePath);
        }

        // remove the tag directory
        $this->tagStorage->removeTags($tags);
    }

    /**
     * Associate the given cache ID (something which can be associated with a
     * cache entry which can later be invalidated) with the given tags.
     *
     * The $lifetime should be stored with the $cacheId. When invalidating if
     * the $lifetime > 0 and it has expired, then the cache entry should be
     * considered as having already been invalidated by the caching proxy.
     *
     * NOTE: Often this method could simply be a proxy to StorageInterface#tagCacheId.
     *
     * @param string[] $tags
     * @param mixed $cacheId
     * @param int $lifetime
     *
     * @return void
     */
    public function tagCacheId(array $tags, $contentDigest, $lifetime = null)
    {
        $this->tagStorage->tagCacheId($tags, $contentDigest, $lifetime);
    }
}
