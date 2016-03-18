<?php

/*
 * This file is part of the Glob package.
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DTL\Symfony\HttpCacheTagging\Storage;

use Doctrine\Common\Cache\Cache;
use DTL\Symfony\HttpCacheTagging\StorageInterface;

/**
 * Tag storage implementation which uses the Doctrine Cache library.
 */
class DoctrineCache implements StorageInterface
{
    /**
     * @var Cache
     */
    private $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * TODO: The lifetime applies not to the tag but to the identifier - we
     *       need to serialize the lifetime with the cache entry and store tags
     *       indefintely.
     *
     * {@inheritdoc}
     */
    public function tagCacheId(array $tags, $identifier, $lifetime = null)
    {
        foreach ($tags as $tag) {
            $identifiers = $this->getCacheIds([$tag]);
            $identifiers[] = $identifier;
            $encodedIdentifiers = json_encode(array_unique($identifiers), true);
            $this->cache->save($tag, $encodedIdentifiers, $lifetime);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeTags(array $tags)
    {
        foreach ($tags as $tag) {
            $t = $this->cache->fetch($tag);
            // doctrine does not care if the key does not exist.
            $this->cache->delete($tag);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheIds(array $tags)
    {
        $ret = [];

        foreach ($tags as $tag) {
            $encodedIdentifiers = $this->cache->fetch($tag);

            if (!$encodedIdentifiers) {
                continue;
            }

            $identifiers = json_decode($encodedIdentifiers);

            // this should never happen, so fail loudly.
            if (null === $identifiers) {
                throw new \RuntimeException(sprintf(
                    'Could not decode cache entry, invalid JSON: %s',
                    $encodedIdentifiers
                ));
            }

            $ret = array_merge($ret, $identifiers);
        }

        return $ret;
    }
}
