<?php

/*
 * This file is part of the Symfony Http Cache Tagging package.
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\HttpCache\Tests\Unit\Tag\Storage;

use DTL\Symfony\HttpCacheTagging\Storage\DoctrineCache;

class DoctrineCacheTest extends \PHPUnit_Framework_TestCase
{
    private $cache;
    private $storage;

    public function setUp()
    {
        $this->cache = $this->prophesize('Doctrine\Common\Cache\Cache');
        $this->storage = new DoctrineCache($this->cache->reveal());
    }

    /**
     * It should associate a number of tags with a given cache identifier.
     */
    public function testTagCacheId()
    {
        $identifier = 'abcd';
        $tags = ['one', 'two'];

        $this->cache->fetch('one')->willReturn('[]');
        $this->cache->fetch('two')->willReturn('[]');
        $this->cache->save('one', '["abcd"]')->shouldBeCalled();
        $this->cache->save('two', '["abcd"]')->shouldBeCalled();

        $this->storage->tagContentDigest($tags, $identifier);
    }

    /**
     * It should append to an existing list of tag entries for a given tag.
     */
    public function testTagCacheIdAppend()
    {
        $identifier = 'dcba';
        $tags = ['one'];

        $this->cache->fetch('one')->willReturn('["abcd"]');
        $this->cache->save('one', '["abcd","dcba"]')->shouldBeCalled();

        $this->storage->tagContentDigest($tags, $identifier);
    }

    /**
     * It should throw an exception if the cache contains invalid JSON.
     *
     * @expectedException RuntimeException
     */
    public function testGetCacheIdsInvalidJson()
    {
        $this->cache->fetch('one')->willReturn('["abcd');

        $this->storage->getCacheIds(['one']);
    }

    /**
     * It should remove tags.
     */
    public function testRemoveTags()
    {
        $this->cache->delete('one')->shouldBeCalled();
        $this->cache->delete('two')->shouldBeCalled();
        $this->storage->removeTags(['one', 'two']);
    }

    /**
     * It should return aggregate cache identifiers for a set of tags.
     */
    public function testGetCacheIdsAggregate()
    {
        $this->cache->fetch('one')->willReturn('["abcd"]');
        $this->cache->fetch('two')->willReturn('["1234","xyz"]');

        $ids = $this->storage->getCacheIds(['one', 'two']);
        $this->assertEquals(['abcd', '1234', 'xyz'], $ids);
    }
}
